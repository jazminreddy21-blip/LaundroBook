<?php

require_once __DIR__ . '/../Models/Customer.php';


interface MachineRepoInterface{
    public function getAvailableMachines(): array;
    public function getMachineById(int $machineId): ?array;
    public function updateStatus(int $machineId, string $status): bool;
    public function machineExists(int $machineId): bool;
}

interface SlotRepoInterface{

    public function getActiveSlots(): array;
    public function getSlotById(int $slotId): ?array;

}

interface CustomerRepoInterface{
    public function findByEmail(string $email): ?Customer;
    public function createCustomer(string $name, string $email, string $phone, string $address): Customer;

    public function findOrCreate(array $data): Customer;
}

interface BookingRepoInterface{
    public function insert(int $customerId, int $managerId, array $service, array $data): int;

    public function getBookedCombosForDate(string $bookingDate): array;
    public function getPrimaryManager(): array;
    public function findBooking(int $bookingId): ?array;
}

interface ServiceRepoInterface{
    public function findByType(string $washType, string $loadType): ?array;
    public function getServiceById(int $serviceId): ?array;
}