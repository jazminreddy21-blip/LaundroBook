<?php

/**
 * BookingRepoInterface includes getBookingsPastEndTime() and
 * markCompleted(), the two methods needed to actually implement the
 * Polling-Based Machine Release fix, used by
 * AvailabilityService::releaseExpiredMachines(). It also includes the
 * admin-facing methods AdminController relies on for the management
 * pages (bookings, reports, etc.), both sets are required, not
 * alternatives to each other.
 */

require_once __DIR__ . '/../Models/Customer.php'; // needed for the Customer type hint below

interface MachineRepoInterface
{
    public function getAvailableMachines(): array;
    public function getMachineById(int $machineId): ?array;
    public function updateStatus(int $machineId, string $status): bool;
    public function machineExists(int $machineId): bool;
    public function getAllMachines(): array;
    public function countByStatus(string $status): int;
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
    public function getAllCustomers(string $search = ''): array;
    public function countAll(): int;
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

    // Admin-facing methods, used by AdminController/DashboardService
    // for the dashboard stats, booking management page, and reports.
    public function todaysBookings(): int;
    public function pendingBookingsCount(): int;
    public function todaysRevenue(): float;
    public function getAllBookings(array $filters = []): array;
    public function findBookingByReference(string $reference): ?array;
    public function updateStatus(int $bookingId, string $status): bool;
    public function revenueBetween(string $startDate, string $endDate): float;
    public function countByStatusBetween(string $status, string $startDate, string $endDate): int;
}

interface ServiceRepoInterface
{
    public function findByType(string $washType, string $loadType): ?array;
    public function getServiceById(int $serviceId): ?array;
    public function getAllServices(): array;
}

interface SystemManagerRepoInterface
{
    public function findManager(string $username): ?SystemManager;
}

interface DeliveryRepoInterface
{
    public function todaysCountByType(string $type): int;
    public function getAll(array $filters = []): array;
    public function findById(int $deliveryId): ?array;
    public function updateStatus(int $deliveryId, string $status): bool;
}

interface EnquiryRepoInterface
{
    // Subject and message are combined into one value before this is
    // called, see EnquiryRepo::insert() for exactly how.
    public function insert(string $name, string $email, string $message): int;
}