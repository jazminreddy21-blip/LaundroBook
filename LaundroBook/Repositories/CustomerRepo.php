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

    // Inserts a brand new customer row and hands back a Customer object
    // built with the ID MySQL generated for it.
    public function createCustomer(string $name, string $email, string $phone, string $address): Customer
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
            return $existing;
        }

        return $this->createCustomer(
            $data['customer_name'],
            $data['customer_email'],
            $data['customer_phone'],
            $data['delivery_address'] ?? null
        );
    }
}