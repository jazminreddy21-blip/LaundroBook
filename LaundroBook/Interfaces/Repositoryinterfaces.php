<?php

/**
 * BookingRepoInterface includes getBookingsPastEndTime() and
 * markCompleted(), the two methods needed to actually implement the
 * Polling-Based Machine Release fix, used by
 * AvailabilityService::releaseExpiredMachines(). It also includes the
 * admin-facing methods AdminController relies on for the management
 * pages (bookings, reports, etc.) - both sets are required, not
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
    public function findById(int $customerId): ?Customer;
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

    // Updates every booking row sharing the same customer+machine+date
    // +service as $bookingId - see BookingRepo for why this matters
    // specifically for Heavy Wash's two-row bookings.
    public function updateStatusForGroup(int $bookingId, string $status): bool;
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

    // Whichever admin currently has the lowest manager_id is the
    // super admin - see SystemManagerRepo for the full reasoning.
    public function isSuperAdmin(int $managerId): bool;

    // Used by the super admin's "Add Admin" feature.
    public function createManager(string $username, string $passwordHash): int;

    // Used by the "Manage Admins" list and to check the last-admin
    // safety rule before allowing a removal.
    public function getAllManagers(): array;
    public function countAll(): int;

    /// Blocks deleting an admin attached to real data (booking/machine/
    // service/slot/enquiry all have a NOT NULL manager_id). In
    // practice this is also what stops the super admin being deleted,
    // since they're the only admin ever attached to anything.
    public function hasAssociatedRecords(int $managerId): bool;

    public function deleteManager(int $managerId): bool;
}

interface DeliveryRepoInterface
{
    // Creates the delivery/collection row a booking needs once it's
    // confirmed - called by BookingService right after the booking
    // itself commits, for any booking where collection_method is
    // 'delivery' or 'collection' (matching check_delivery_type).
    public function insert(int $bookingId, int $groundworkerId, string $deliveryType, string $scheduledTime): int;
    public function todaysCountByType(string $type): int;
    public function getAll(array $filters = []): array;
    public function findById(int $deliveryId): ?array;

    // Used by the customer-facing tracking pages - a customer only
    // knows their booking_reference, not a delivery_id, so lookup
    // starts from the booking side. Needs the expected leg type too,
    // since one booking can now have both a collection and a
    // delivery row.
    public function findByBookingId(int $bookingId, string $deliveryType): ?array;
    public function updateStatus(int $deliveryId, string $status): bool;

    // Admin's manual reassignment of which groundworker is handling a
    // delivery - separate from updateStatus(), same one-concern-per-
    // method pattern as the rest of this interface.
    public function reassignGroundworker(int $deliveryId, int $groundworkerId): bool;
}

interface EnquiryRepoInterface
{
    // Subject and message are combined into one value before this is
    // called, see EnquiryRepo::insert() for exactly how.
    public function insert(string $name, string $email, string $message): int;

    // Used by the admin Enquiry Management page.
    public function getAll(string $search = ''): array;

    // Used by DashboardService for the "pending enquiries" system
    // notification - matches the naming convention already used by
    // MachineRepoInterface::countByStatus().
    public function countByStatus(string $status): int;

    // enquiry.status has no CHECK constraint in the schema, unlike
    // booking.status - the allowed values (Pending/Responded) are only
    // enforced here in code, matching how the column was already
    // being used with a plain 'Pending' default at insert time.
    public function updateStatus(int $enquiryId, string $status): bool;
}