<?php
/*
    Mock confirm booking.php

    Full stand-in for bookingControllers real wiring, using stub
    repositories, exercises the ENTIRE confirmBooking() flow
    (validation, price lookup, availability re-check, the
    "transaction", customer/booking creation, and now the receipt
    lookups for machine name and slot label) with zero real database
    connection. Nothing here ever calls Connection::getConnection().

    HOW TO USE: temporarily point your booking form action at this
    file instead of the real bookingController.php:

        <form action="../tests/Mock confirm booking.php" ...>

    Submit the form as normal. On "success" it will redirect to
    BookingConfirm.php exactly like the real flow would, with fake but
    complete receipt data. Switch the form action back to
    bookingController.php once you're ready to test against a real
    database - nothing else needs to change, since this file builds
    the exact same bookingController class with the same constructor
    shape, just with fake dependencies instead of real ones.

    
*/

require_once __DIR__ . '/../Controllers/bookingController.php';
require_once __DIR__ . '/../Interfaces/Repositoryinterfaces.php';
require_once __DIR__ . '/../Services/AvailabilityService.php';
require_once __DIR__ . '/../Services/BookingService.php';

class StubMachineRepo implements MachineRepoInterface
{
    
    private array $machines = [
        1 => ['machine_id' => 1, 'machine_name' => 'Machine 1', 'machine_status' => 'available'],
        3 => ['machine_id' => 3, 'machine_name' => 'Machine 3', 'machine_status' => 'available'],
    ];

    public function getAvailableMachines(): array
    {
        return array_values($this->machines);
    }
    public function getMachineById(int $machineId): ?array
    {
        return $this->machines[$machineId] ?? null;
    }
    public function updateStatus(int $machineId, string $status): bool { return true; }
    public function machineExists(int $machineId): bool
    {
        return isset($this->machines[$machineId]);
    }
}

class StubSlotRepo implements SlotRepoInterface
{
    
    private array $slots = [
        1 => ['slot_id' => 1, 'slot_label' => '08:00 - 08:45', 'start_time' => '08:00', 'end_time' => '08:45', 'is_active' => 1],
        2 => ['slot_id' => 2, 'slot_label' => '08:45 - 09:30', 'start_time' => '08:45', 'end_time' => '09:30', 'is_active' => 1],
        3 => ['slot_id' => 3, 'slot_label' => '09:30 - 10:15', 'start_time' => '09:30', 'end_time' => '10:15', 'is_active' => 1],
    ];

    public function getActiveSlots(): array
    {
        return array_values($this->slots);
    }
    public function getSlotById(int $slotId): ?array
    {
        return $this->slots[$slotId] ?? null;
    }
}

class StubCustomerRepo implements CustomerRepoInterface
{
    public function findByEmail(string $email): ?Customer { return null; }
    public function createCustomer(string $name, string $email, string $phone, string $address): Customer
    {
        return new Customer(1, $name, $email, $phone, $address);
    }
    public function findOrCreate(array $data): Customer
    {
        return new Customer(1, $data['customer_name'], $data['customer_email'], $data['customer_phone'], $data['delivery_address'] ?? null);
    }
}

class StubBookingRepo implements BookingRepoInterface
{
    public function insert(int $customerId, int $managerId, array $service, array $data): int
    {
        // fake booking_id - real one only exists once a real INSERT runs
        return 1;
    }
    // Nothing "already booked" here, so every combo the availability
    // check runs against will come back free.
    public function getBookedCombosForDate(string $bookingDate): array { return []; }
    public function getPrimaryManager(): array { return ['manager_id' => 1]; }
    public function findBooking(int $bookingId): ?array { return null; }
}

class StubServiceRepo implements ServiceRepoInterface
{
    // Mirrors your real Service Pricing table exactly, so createBooking()
    // actually succeeds instead of failing on "invalid combination."
    private array $pricing = [
        'quick_clothes'   => ['price' => 30.00, 'duration_minutes' => 25, 'duration_slots' => 1],
        'quick_beddings'  => ['price' => 40.00, 'duration_minutes' => 25, 'duration_slots' => 1],
        'quick_towels'    => ['price' => 35.00, 'duration_minutes' => 25, 'duration_slots' => 1],
        'normal_clothes'  => ['price' => 40.00, 'duration_minutes' => 35, 'duration_slots' => 1],
        'normal_beddings' => ['price' => 50.00, 'duration_minutes' => 35, 'duration_slots' => 1],
        'normal_towels'   => ['price' => 45.00, 'duration_minutes' => 35, 'duration_slots' => 1],
        'heavy_clothes'   => ['price' => 55.00, 'duration_minutes' => 65, 'duration_slots' => 2],
        'heavy_beddings'  => ['price' => 70.00, 'duration_minutes' => 65, 'duration_slots' => 2],
        'heavy_towels'    => ['price' => 65.00, 'duration_minutes' => 65, 'duration_slots' => 2],
    ];

    public function findByType(string $washType, string $loadType): ?array
    {
        $key = $washType . '_' . $loadType;
        if (!isset($this->pricing[$key])) {
            return null;
        }
        return array_merge(['service_id' => 1, 'wash_type' => $washType, 'load_type' => $loadType], $this->pricing[$key]);
    }

    public function getServiceById(int $serviceId): ?array { return null; }
}

//Build the controller with fakes and run it, same as real wiring

$machineRepo = new StubMachineRepo();
$slotRepo = new StubSlotRepo();
$customerRepo = new StubCustomerRepo();
$bookingRepo = new StubBookingRepo();
$serviceRepo = new StubServiceRepo();

$availability = new AvailabilityService($machineRepo, $slotRepo, $bookingRepo);
$bookingService = new BookingService($machineRepo, $slotRepo, $customerRepo, $bookingRepo, $serviceRepo, $availability);

// bookingController takes one BookingService

$controller = new bookingController($bookingService);
$controller->confirmBooking();