<?php

require_once __DIR__ . '/../Models/Customer.php';


interface MachineRepoInterface{
    public function getAvailableMachines(): array;
    public function getMachineById(int $machineId): ?array;
    public function updateStatus(int $machineId, string $status): bool;
    public function machineExists(int $machineId): bool;
    public function getAllMachines(): array;
    public function countByStatus(string $status): int;
}

interface SlotRepoInterface{

    public function getActiveSlots(): array;
    public function getSlotById(int $slotId): ?array;

}

interface CustomerRepoInterface{
    public function findByEmail(string $email): ?Customer;
    public function createCustomer(string $name, string $email, string $phone, string $address): Customer;

    public function findOrCreate(array $data): Customer;
    public function getAllCustomers(string $search = ''): array;
    public function countAll(): int;
}

interface BookingRepoInterface{
    public function insert(int $customerId, int $managerId, array $service, array $data): int;

    public function getBookedCombosForDate(string $bookingDate): array;
    public function getPrimaryManager(): array;
    public function findBooking(int $bookingId): ?array;
    public function todaysBookings(): int; 
    public function pendingBookingsCount(): int;
    public function todaysRevenue(): float;
    public function getAllBookings(array $filters = []): array;
    public function findBookingByReference(string $reference): ?array;
    public function updateStatus(int $bookingId, string $status): bool;
    public function revenueBetween(string $startDate, string $endDate): float;
    public function countByStatusBetween(string $status, string $startDate, string $endDate): int;
}

interface ServiceRepoInterface{
    public function findByType(string $washType, string $loadType): ?array;
    public function getServiceById(int $serviceId): ?array;
    public function getAllServices(): array;
}

interface SystemManagerRepoInterface{
    public function findManager(string $username): ?SystemManager; 
}

interface DeliveryRepoInterface{
    public function todaysCountByType(string $type): int;
    public function getAll(array $filters = []): array;
    public function findById(int $deliveryId): ?array;
    public function updateStatus(int $deliveryId, string $status): bool;
}