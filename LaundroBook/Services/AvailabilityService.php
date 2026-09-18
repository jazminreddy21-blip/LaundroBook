<?php

require_once __DIR__ . '/../Interfaces/Repositoryinterfaces.php';

/**
 * AvailabilityService
 *
 * The one place Machine and Slot are allowed to meet. Type-hinted
 * against interfaces (not concrete repository classes), so this can be
 * built with real or stub repositories interchangeably.
 *
 * ONE EXCEPTION TO READ-ONLY: releaseExpiredMachines() writes to the
 * database, but only for bookings that have objectively finished - no
 * user input involved, so it's safe even on the public availability
 * check. Runs here so it fires on every check, not just at booking time.
 */
class AvailabilityService
{
    private MachineRepoInterface $machines;
    private SlotRepoInterface $slots;
    private BookingRepoInterface $bookings;

    // How close to a slot's start time someone can still book it.
    // 08:00 already starting doesn't just mean "started" - a customer
    // also needs time to actually walk over, so this excludes slots
    // starting soon, not just ones already in progress. Adjust this
    // single number if 30 minutes turns out to be too tight or too
    // generous once this is tested against real usage.
    private const BOOKING_BUFFER_MINUTES = 30;

    public function __construct(
        MachineRepoInterface $machines,
        SlotRepoInterface $slots,
        BookingRepoInterface $bookings
    ) {
        $this->machines = $machines;
        $this->slots = $slots;
        $this->bookings = $bookings;
    }

    /**
     * THE ACTUAL FIX for Polling-Based Machine Release (Analysis Phase
     * Section 3.5, High severity). Finds every booking whose slot has
     * genuinely finished (real booking_date + slot end_time compared
     * against the current moment, not just a bare time-of-day check),
     * flips that booking's machine back to 'available', and marks the
     * booking 'completed'.
     *
     * Called at the top of getAvailableCombos() below, so it runs on
     * every availability check - matching the original design intent
     * of a background check that fires on page load, not something
     * that depends on an admin manually noticing and fixing it.
     */
    public function releaseExpiredMachines(): void
    {
        $finishedBookings = $this->bookings->getBookingsPastEndTime();

        foreach ($finishedBookings as $booking) {
            $this->machines->updateStatus((int)$booking['machine_id'], 'available');
            $this->bookings->markCompleted((int)$booking['booking_id']);
        }
    }

    /**
     * Only matters for TODAY's date - a slot's start_time being "too
     * soon" is meaningless for a booking date that hasn't arrived yet,
     * so this always returns true for any future date.
     */
    private function isSlotBookable(array $slot, string $bookingDate): bool
    {
        if ($bookingDate !== date('Y-m-d')) {
            return true;
        }

        $cutoff = time() + (self::BOOKING_BUFFER_MINUTES * 60);
        return strtotime($slot['start_time']) >= $cutoff;
    }

    /**
     * Returns a grid of machine/slot combinations that are free on the
     * given date, taking duration_slots into account (Heavy Wash needs
     * two consecutive free slots on the same machine).
     */
    public function getAvailableCombos(string $bookingDate, int $durationSlots = 1): array
    {
        // Run the release check first, so machine_status is accurate
        // before anything below reads it.
        $this->releaseExpiredMachines();

        $availableMachines = $this->machines->getAvailableMachines();
        $activeSlots = $this->slots->getActiveSlots(); // expected sorted by start_time
        $bookedCombos = $this->bookings->getBookedCombosForDate($bookingDate);

        $taken = [];
        foreach ($bookedCombos as $combo) {
            $taken["{$combo['machine_id']}:{$combo['slot_id']}"] = true;
        }

        $results = [];

        foreach ($availableMachines as $machine) {
            foreach ($activeSlots as $index => $slot) {
                // Skip slots that are already in progress or starting
                // too soon today - see isSlotBookable() above.
                if (!$this->isSlotBookable($slot, $bookingDate)) {
                    continue;
                }

                if ($durationSlots === 1) {
                    if (!isset($taken["{$machine['machine_id']}:{$slot['slot_id']}"])) {
                        $results[] = [
                            'machine_id' => $machine['machine_id'],
                            'machine_name' => $machine['machine_name'],
                            'slot_id' => $slot['slot_id'],
                            'slot_label' => $slot['slot_label'],
                        ];
                    }
                    continue;
                }

                // Heavy Wash: this slot AND the next one must both be free
                // (and bookable) on the same machine, and a "next slot"
                // must exist.
                $nextSlot = $activeSlots[$index + 1] ?? null;
                if ($nextSlot === null || !$this->isSlotBookable($nextSlot, $bookingDate)) {
                    continue;
                }

                $thisFree = !isset($taken["{$machine['machine_id']}:{$slot['slot_id']}"]);
                $nextFree = !isset($taken["{$machine['machine_id']}:{$nextSlot['slot_id']}"]);

                if ($thisFree && $nextFree) {
                    $results[] = [
                        'machine_id' => $machine['machine_id'],
                        'machine_name' => $machine['machine_name'],
                        'slot_id' => $slot['slot_id'],
                        'slot_label' => $slot['slot_label'],
                        'second_slot_id' => $nextSlot['slot_id'],
                    ];
                }
            }
        }

        return $results;
    }

    /**
     * Final check run right before insert in BookingService.
     * Re-verifies the specific combo the customer picked is still free
     * AND still bookable time-wise - a customer who leaves the page
     * open for 40 minutes before clicking Confirm shouldn't be able to
     * slip a now-past slot through just because it looked fine when
     * the page first loaded.
     */
    public function isComboStillFree(int $machineId, int $slotId, string $bookingDate): bool
    {
        $slot = $this->slots->getSlotById($slotId);
        if ($slot === null || !$this->isSlotBookable($slot, $bookingDate)) {
            return false;
        }

        $bookedCombos = $this->bookings->getBookedCombosForDate($bookingDate);

        foreach ($bookedCombos as $combo) {
            if ((int)$combo['machine_id'] === $machineId && (int)$combo['slot_id'] === $slotId) {
                return false;
            }
        }

        return true;
    }
}