<?php

require_once __DIR__ . '/../Interfaces/Repositoryinterfaces.php';
require_once __DIR__ . '/../Repositories/BookingRepo.php';
require_once __DIR__ . '/../Repositories/DeliveryRepo.php';
require_once __DIR__ . '/../Repositories/ServiceRepo.php';
require_once __DIR__ . '/../Repositories/MachineRepo.php';
require_once __DIR__ . '/../Repositories/SlotRepo.php';

// TrackingController
// This is what TrackingDel.php, TrackingPick.php, and TrackingOrder.php
// call when a customer looks up their order by booking_reference.
// Deliberately no authentication - matches AvailabilityController's
// reasoning: read-only, never touches or exposes anything beyond a
// single booking's own status, and a customer needs no account to
// use it.
class TrackingController
{
    private BookingRepoInterface $bookingRepo;
    private DeliveryRepoInterface $deliveryRepo;
    private ServiceRepoInterface $serviceRepo;
    private MachineRepoInterface $machineRepo;
    private SlotRepoInterface $slotRepo;

    public function __construct(
        BookingRepoInterface $bookingRepo,
        DeliveryRepoInterface $deliveryRepo,
        ServiceRepoInterface $serviceRepo,
        MachineRepoInterface $machineRepo,
        SlotRepoInterface $slotRepo
    ) {
        $this->bookingRepo = $bookingRepo;
        $this->deliveryRepo = $deliveryRepo;
        $this->serviceRepo = $serviceRepo;
        $this->machineRepo = $machineRepo;
        $this->slotRepo = $slotRepo;
    }

    // Used by TrackingOrder.php - the general wash-status tracker.
    // Unlike track() below, this works from the booking alone with no
    // dependency on a delivery row existing at all, so it's the ONLY
    // tracking available to a "Self Drop off and Pickup" customer,
    // who never gets a delivery row created for them in the first
    // place. A "Home Pickup and Delivery" customer can use this too,
    // alongside the delivery/pickup-specific pages for the logistics
    // side - this page answers "is my wash done", a genuinely
    // different question from "where's my delivery driver".
    public function trackOrder(string $reference): array
    {
        $reference = trim($reference);

        if ($reference === '') {
            return ['error' => 'Please enter a booking reference.'];
        }

        $booking = $this->bookingRepo->findBookingByReference($reference);

        if ($booking === null) {
            return ['error' => 'No booking was found with that reference. Please check and try again.'];
        }

        $service = $this->serviceRepo->getServiceById((int)$booking['service_id']);
        $machine = $this->machineRepo->getMachineById((int)$booking['machine_id']);
        $slot = $this->slotRepo->getSlotById((int)$booking['slot_id']);

        // If this booking also has a delivery or collection leg (a
        // "Home Pickup and Delivery" booking), point the customer to
        // the specific page for that too - purely informational, this
        // page still works fully without it.
        $hasDelivery = $this->deliveryRepo->findByBookingId((int)$booking['booking_id'], 'delivery') !== null;
        $hasCollection = $this->deliveryRepo->findByBookingId((int)$booking['booking_id'], 'collection') !== null;

        return [
            'booking' => $booking,
            'service' => $service,
            'machine' => $machine,
            'slot' => $slot,
            'has_delivery' => $hasDelivery,
            'has_collection' => $hasCollection,
        ];
    }

    // $expectedType is 'delivery' or 'collection' - lets
    // TrackingDel.php and TrackingPick.php share this one method while
    // each only ever showing results that actually match what that
    // page is for. Returns an array with either 'error' or 'booking'/
    // 'delivery'/'service'/'collection_method' keys - never throws,
    // since a wrong/unknown reference typed by a customer is an
    // expected, normal case, not a bug.
    public function track(string $reference, string $expectedType): array
    {
        $reference = trim($reference);

        if ($reference === '') {
            return ['error' => 'Please enter a booking reference.'];
        }

        $booking = $this->bookingRepo->findBookingByReference($reference);

        if ($booking === null) {
            return ['error' => 'No booking was found with that reference. Please check and try again.'];
        }

        $bookingId = (int)$booking['booking_id'];
        $delivery = $this->deliveryRepo->findByBookingId($bookingId, $expectedType);

        if ($delivery === null) {
            // Not this leg - but a "Home Pickup and Delivery" booking
            // can have the OTHER leg instead, so check that before
            // deciding this booking genuinely has no tracking at all.
            // Gives an accurate, specific message either way, rather
            // than assuming which case this is.
            $otherType = $expectedType === 'delivery' ? 'collection' : 'delivery';
            $otherLeg = $this->deliveryRepo->findByBookingId($bookingId, $otherType);

            if ($otherLeg !== null) {
                $otherLabel = $otherType === 'delivery' ? 'a delivery' : 'a pickup';
                return ['error' => "That booking reference belongs to {$otherLabel}, not this page. Please use the correct tracking page."];
            }

            return ['error' => 'This booking has no delivery or pickup tracking associated with it.'];
        }

        $service = $this->serviceRepo->getServiceById((int)$booking['service_id']);

        // collection_method isn't actually a stored column anywhere in
        // the schema - but reaching this point (a real $delivery row
        // was found) is only possible for a "Home Pickup and
        // Delivery" booking, since "Self Drop off and Pickup" never
        // creates any delivery rows at all (see BookingService) - if
        // this booking had been self-service, the check above would
        // already have returned the "no tracking" error instead.
        $collectionMethod = 'Home Pickup and Delivery';

        return [
            'booking' => $booking,
            'delivery' => $delivery,
            'service' => $service,
            'collection_method' => $collectionMethod,
        ];
    }
}