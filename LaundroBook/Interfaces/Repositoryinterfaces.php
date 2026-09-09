<?php

/**
 
 * BookingRepoInterface now includes getBookingsPastEndTime()
 * and markCompleted(), the two methods needed to actually implement
 * the Polling-Based Machine Release fix,used by AvailabilityService::releaseExpiredMachines().
 */

require_once __DIR__ . '/../Models/Customer.php'; // needed for the Customer type hint below

interface MachineRepoInterface
{
    public function getAvailableMachines(): array;
    public function getMachineById(int $machineId): ?array;
    public function updateStatus(int $machineId, string $status): bool;
    public function machineExists(int $machineId): bool;
}

interface SlotRepoInterface
{
    // Must return slots sorted by start_time - AvailabilityService relies
    // on array order to find the "next" slot for Heavy Wash bookings.
    public function getActiveSlots(): array;
    public function getSlotById(int $slotId): ?array;
}

interface CustomerRepoInterface
{
    public function findByEmail(string $email): ?Customer;
    public function createCustomer(string $name, string $email, string $phone, ?string $address): Customer;

    // Reuses an existing customer by email, or creates a new one.
    public function findOrCreate(array $data): Customer;
}

interface BookingRepoInterface
{
    public function insert(int $customerId, int $managerId, array $service, array $data): int;

    // Returns [['machine_id' => .., 'slot_id' => ..], ...] for a given
    // date, excluding cancelled bookings. Used by AvailabilityService.
    public function getBookedCombosForDate(string $bookingDate): array;

    public function getPrimaryManager(): array;
    public function findBooking(int $bookingId): ?array;

    // Returns [['booking_id' => .., 'machine_id' => ..], ...] for every
    // still-Pending booking whose slot has genuinely finished (real
    // date + slot end_time combined, compared against NOW()). This is
    // what AvailabilityService::releaseExpiredMachines() uses to know
    // which machines to flip back to available.
    public function getBookingsPastEndTime(): array;

    // Marks a booking completed once its slot has ended.
    public function markCompleted(int $bookingId): bool;
}

interface ServiceRepoInterface
{
    public function findByType(string $washType, string $loadType): ?array;
    public function getServiceById(int $serviceId): ?array;
}