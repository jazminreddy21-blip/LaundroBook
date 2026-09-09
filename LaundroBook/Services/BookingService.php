<?php

require_once __DIR__ . '/../Interfaces/Repositoryinterfaces.php';
require_once __DIR__ . '/../Interfaces/EmailServiceInterface.php';
require_once __DIR__ . '/AvailabilityService.php';
require_once __DIR__ . '/../Database/Connection.php';

/**
 * BookingService
 *
 * This is the only class that coordinates Machine, Slot, Customer, Booking, and Service
 * together, and the only place the transaction lives. bookingController
 * should only ever call createBooking() and relay whatever comes back -
 * it should never touch a repository directly.
 *
 * This existed as a private method inside bookingController for a
 * while, since nothing else needed to create a booking. 
 */
class BookingService
{
    private MachineRepoInterface $machineRepo;
    private SlotRepoInterface $slotRepo;
    private CustomerRepoInterface $customerRepo;
    private BookingRepoInterface $bookingRepo;
    private ServiceRepoInterface $serviceRepo;
    private AvailabilityService $availability;
    private EmailServiceInterface $emailService;
    private mysqli $db;

    public function __construct(
        MachineRepoInterface $machineRepo,
        SlotRepoInterface $slotRepo,
        CustomerRepoInterface $customerRepo,
        BookingRepoInterface $bookingRepo,
        ServiceRepoInterface $serviceRepo,
        AvailabilityService $availability,
        EmailServiceInterface $emailService
    ) {
        $this->machineRepo = $machineRepo;
        $this->slotRepo = $slotRepo;
        $this->customerRepo = $customerRepo;
        $this->bookingRepo = $bookingRepo;
        $this->serviceRepo = $serviceRepo;
        $this->availability = $availability;
        $this->emailService = $emailService;
        $this->db = Connection::getConnection();
    }

    /**
     * $data is expected to already be validated/sanitized by
     * bookingController::validate_input() before this is called.
     * Expected keys: customer_name, customer_email, customer_phone,
     * delivery_address, wash_type, load_type, booking_date, machine_id,
     * slot_id, and second_slot_id (only present for a Heavy Wash combo).
     */
    public function createBooking(array $data): array
    {
        $service = $this->serviceRepo->findByType($data['wash_type'], $data['load_type']);
        if ($service === null) {
            return ['success' => false, 'errors' => ['Invalid wash type / load type combination']];
        }

        if (!$this->machineRepo->machineExists($data['machine_id'])) {
            return ['success' => false, 'errors' => ['Selected machine does not exist']];
        }

        // Final race-condition guard - re-check right before writing,
        // since time has passed since the customer saw this as available.
        if (!$this->availability->isComboStillFree($data['machine_id'], $data['slot_id'], $data['booking_date'])) {
            return ['success' => false, 'errors' => ['Selected slot was just taken, please choose another']];
        }

        // Heavy Wash needs its second slot re-checked too - previously
        // only the first slot was verified here, and the second slot's
        // insert relied entirely on the active_combo_key constraint to
        // catch a stale request, which works but opens a transaction
        // and rolls it back unnecessarily. This check catches it early,
        // before any transaction is opened at all.
        if (!empty($data['second_slot_id'])
            && !$this->availability->isComboStillFree($data['machine_id'], (int)$data['second_slot_id'], $data['booking_date'])) {
            return ['success' => false, 'errors' => ['Second slot required for Heavy Wash was just taken, please choose another']];
        }

        $manager = $this->bookingRepo->getPrimaryManager();

        $this->db->begin_transaction();
        try {
            $customer = $this->customerRepo->findOrCreate($data);

            $bookingId = $this->bookingRepo->insert(
                $customer->getCustomerId(),
                (int)$manager['manager_id'],
                $service,
                $data
            );

            // Heavy Wash occupies two consecutive slots, lock the second
            // one with its own booking row so it also shows as taken.
            if ((int)$service['duration_slots'] === 2 && !empty($data['second_slot_id'])) {
                $secondData = $data;
                $secondData['slot_id'] = $data['second_slot_id'];

                $this->bookingRepo->insert(
                    $customer->getCustomerId(),
                    (int)$manager['manager_id'],
                    $service,
                    $secondData
                );
            }

            // This flip is now genuinely accurate and actively
            // maintained - AvailabilityService::releaseExpiredMachines()
            // flips it back to 'available' once this booking's slot has
            // finished, so an admin dashboard can trust it as a live
            // status. It still doesn't gate whether THIS machine can be
            // booked for a different date, since machine_status has no
            // date attached - that per-date decision is always made
            // separately by AvailabilityService against real booking rows.
            $this->machineRepo->updateStatus($data['machine_id'], 'in_use');

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollback();

            // MySQL error 1062 = duplicate entry, this is what fires if
            // active_combo_key's UNIQUE constraint catches a genuine
            // race condition - two requests both passing the earlier
            // isComboStillFree() check before either one's INSERT
            // completes. Everything else falls through to the generic
            // message below.
            if ((int)$e->getCode() === 1062) {
                return ['success' => false, 'errors' => ['Selected slot was just taken, please choose another']];
            }

            return ['success' => false, 'errors' => ['Booking could not be saved, please try again']];
        }

        $reference = 'LB-' . str_pad((string)$bookingId, 5, '0', STR_PAD_LEFT);

        // Looked up here, after the transaction commits, purely for the
        // receipt page - none of this affects whether the booking
        // itself succeeded.
        $machine = $this->machineRepo->getMachineById($data['machine_id']);
        $slot = $this->slotRepo->getSlotById($data['slot_id']);
        $secondSlot = !empty($data['second_slot_id'])
            ? $this->slotRepo->getSlotById($data['second_slot_id'])
            : null;

        // Fire-and-forget, as required by Function 5 in Section 4.6 -
        // this runs AFTER commit, so nothing it does can affect whether
        // the booking itself succeeded. EmailService catches its own
        // exceptions and always returns a bool rather than throwing,
        // but the result is deliberately ignored here anyway - a
        // customer's booking is successful the moment this method
        // returns success, independent of whether the email happens to
        // send.
        $this->emailService->sendBookingConfirmation(
            $data['customer_email'],
            $reference,
            $service,
            $data['booking_date'],
            $machine['machine_name'] ?? '',
            $slot['slot_label'] ?? '',
            $secondSlot['slot_label'] ?? null
        );

        return [
            'success' => true,
            'booking_id' => $bookingId,
            'booking_reference' => $reference,
            'total_price' => $service['price'],
            'wash_type' => $service['wash_type'],
            'load_type' => $service['load_type'],
            'duration_minutes' => $service['duration_minutes'],
            'machine_name' => $machine['machine_name'] ?? null,
            'slot_label' => $slot['slot_label'] ?? null,
            'second_slot_label' => $secondSlot['slot_label'] ?? null,
        ];
    }
}