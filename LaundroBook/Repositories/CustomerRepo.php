<?php

require_once __DIR__ . '/../Database/Connection.php';
require_once __DIR__ . '/../Interfaces/Repositoryinterfaces.php';
require_once __DIR__ . '/../Models/Customer.php';

class CustomerRepo implements CustomerRepoInterface
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Connection::getConnection();
    }

    private function run(string $sql, string $types = '', array $params = []): mysqli_stmt
    {
        $stmt = $this->db->prepare($sql);
        if ($types !== '') {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt;
    }

    // Looks up a customer by email. Returns null if no match, since
    // customer.customer_email is UNIQUE, there can only ever be zero
    // or one result, never more than one.
    public function findByEmail(string $email): ?Customer
    {
        $sql = "SELECT customer_id, customer_name, customer_email, customer_phone, address
                FROM customer WHERE customer_email = ?";

        $stmt = $this->run($sql, 's', [$email]);
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) {
            return null;
        }

        return new Customer(
            $row['customer_id'],
            $row['customer_name'],
            $row['customer_email'],
            $row['customer_phone'],
            $row['address']
        );
    }

    // Used by AdminController::updateBookingStatus() - a booking row
    // only has customer_id, so this is how the completion email
    // actually gets the customer's real email address.
    public function findById(int $customerId): ?Customer
    {
        $sql = "SELECT customer_id, customer_name, customer_email, customer_phone, address
                FROM customer WHERE customer_id = ?";

        $stmt = $this->run($sql, 'i', [$customerId]);
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) {
            return null;
        }

        return new Customer(
            $row['customer_id'],
            $row['customer_name'],
            $row['customer_email'],
            $row['customer_phone'],
            $row['address']
        );
    }

    // Inserts a brand new customer row and hands back a Customer object
    // built with the ID MySQL generated for it.
    public function createCustomer(string $name, string $email, string $phone, ?string $address): Customer
    {
        $sql = "INSERT INTO customer (customer_name, customer_email, customer_phone, address)
                VALUES (?, ?, ?, ?)";

        $stmt = $this->run($sql, 'ssss', [$name, $email, $phone, $address]);
        $newId = $stmt->insert_id;
        $stmt->close();

        return new Customer($newId, $name, $email, $phone, $address);
    }

    // This is the one BookingService actually calls. Reuses an
    // existing customer by email if one's already in the system,
    // otherwise creates a new record. This keeps the
    // booking flow working for repeat customers without
    // creating duplicate customer rows every time they book again,
    // i remember this was one of the issues discussed.
    public function findOrCreate(array $data): Customer
    {
        $existing = $this->findByEmail($data['customer_email']);
        if ($existing !== null) {
            $newName = $data['customer_name'];
            $newPhone = $data['customer_phone'];
            $newAddress = $data['delivery_address'] ?? null;

            $nameChanged = $newName !== $existing->getCustomerName();
            $phoneChanged = $newPhone !== $existing->getCustomerPhone();
            $addressChanged = $newAddress !== null && $newAddress !== $existing->getAddress();

            if ($nameChanged || $phoneChanged || $addressChanged) {
                $finalAddress = $addressChanged ? $newAddress : $existing->getAddress();
                $this->updateDetails($existing->getCustomerId(), $newName, $newPhone, $finalAddress);

                return new Customer(
                    $existing->getCustomerId(),
                    $newName,
                    $existing->getCustomerEmail(),
                    $newPhone,
                    $finalAddress
                );
            }

            return $existing;
        }

        return $this->createCustomer(
            $data['customer_name'],
            $data['customer_email'],
            $data['customer_phone'],
            $data['delivery_address'] ?? null
        );
    }

    private function updateDetails(int $customerId, string $name, string $phone, ?string $address): bool
    {
        $stmt = $this->run(
            "UPDATE customer SET customer_name = ?, customer_phone = ?, address = ? WHERE customer_id = ?",
            'sssi',
            [$name, $phone, $address, $customerId]
        );
        $stmt->close();
        return true;
    }

    public function getAllCustomers(string $search = ''): array
    {
        $sql = "SELECT c.customer_id, c.customer_name, c.customer_email, c.customer_phone, c.address,
                       COUNT(b.booking_id) AS total_bookings
                FROM customer c
                LEFT JOIN booking b ON b.customer_id = c.customer_id
                WHERE 1=1";

        $types = '';
        $params = [];

        if ($search !== '') {
            $sql .= " AND (c.customer_name LIKE ? OR c.customer_email LIKE ? OR c.customer_phone LIKE ?)";
            $like = '%' . $search . '%';
            $types = 'sss';
            $params = [$like, $like, $like];
        }

        $sql .= " GROUP BY c.customer_id ORDER BY c.customer_name";

        $stmt = $this->run($sql, $types, $params);
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function countAll(): int
    {
        $result = $this->db->query("SELECT COUNT(*) AS total FROM customer")->fetch_assoc();
        return (int)$result['total'];
    }
}