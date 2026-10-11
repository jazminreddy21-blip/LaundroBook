<?php

require_once __DIR__ . '/../Database/Connection.php';
require_once __DIR__ . '/../Interfaces/Repositoryinterfaces.php';

class DeliveryRepo implements DeliveryRepoInterface
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

    // Called by BookingService right after a booking commits, for any
    // booking where collection_method is 'delivery' or 'collection'.
    // Nothing else in this project currently creates a delivery row -
    // without this being wired in, the delivery table stays empty
    // regardless of what a customer picks at booking time.
    public function insert(int $bookingId, int $groundworkerId, string $deliveryType, string $scheduledTime): int
    {
        $sql = "INSERT INTO delivery (booking_id, groundworker_id, delivery_type, delivery_status, scheduled_time)
                VALUES (?, ?, ?, 'pending', ?)";

        $stmt = $this->run($sql, 'iiss', [$bookingId, $groundworkerId, $deliveryType, $scheduledTime]);
        $deliveryId = $stmt->insert_id;
        $stmt->close();

        return $deliveryId;
    }

    public function todaysCountByType(string $type): int
    {
        $sql = "SELECT COUNT(*) AS total
                FROM delivery d
                JOIN booking b ON d.booking_id = b.booking_id
                WHERE d.delivery_type = ? AND b.booking_date = CURDATE()";
        $stmt = $this->run($sql, 's', [$type]);
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return (int)$result['total'];
    }

    // Joins in exactly what deliveryManagement.php / pickupManagement.php
    // display per row - booking_reference and customer_name/address via
    // booking->customer, groundworker_name via groundworker. $filters
    // always includes delivery_type (set by the two admin pages to
    // 'delivery' or 'collection' respectively) plus optional search/status.
    public function getAll(array $filters = []): array
    {
        $sql = "SELECT d.delivery_id, d.delivery_status, d.scheduled_time, d.groundworker_id,
                       b.booking_reference,
                       c.customer_name, c.address,
                       g.groundworker_name
                FROM delivery d
                JOIN booking b ON d.booking_id = b.booking_id
                JOIN customer c ON b.customer_id = c.customer_id
                JOIN groundworker g ON d.groundworker_id = g.groundworker_id
                WHERE 1=1";

        $types = '';
        $params = [];

        if (!empty($filters['type'])) {
            $sql .= " AND d.delivery_type = ?";
            $types .= 's';
            $params[] = $filters['type'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (b.booking_reference LIKE ? OR c.customer_name LIKE ?)";
            $like = '%' . $filters['search'] . '%';
            $types .= 'ss';
            $params[] = $like;
            $params[] = $like;
        }

        if (!empty($filters['status'])) {
            $sql .= " AND d.delivery_status = ?";
            $types .= 's';
            $params[] = $filters['status'];
        }

        $sql .= " ORDER BY d.scheduled_time DESC";

        $stmt = $this->run($sql, $types, $params);
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function findById(int $deliveryId): ?array
    {
        $sql = "SELECT * FROM delivery WHERE delivery_id = ?";
        $stmt = $this->run($sql, 'i', [$deliveryId]);
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result ?: null;
    }

    public function findByBookingId(int $bookingId, string $deliveryType): ?array
    {
        $sql = "SELECT d.delivery_id, d.delivery_type, d.delivery_status, d.scheduled_time,
                       b.booking_reference,
                       c.customer_name, c.address,
                       g.groundworker_name, g.groundworker_phone
                FROM delivery d
                JOIN booking b ON d.booking_id = b.booking_id
                JOIN customer c ON b.customer_id = c.customer_id
                JOIN groundworker g ON d.groundworker_id = g.groundworker_id
                WHERE d.booking_id = ? AND d.delivery_type = ?";
        $stmt = $this->run($sql, 'is', [$bookingId, $deliveryType]);
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result ?: null;
    }

    public function updateStatus(int $deliveryId, string $status): bool
    {
        $stmt = $this->run("UPDATE delivery SET delivery_status = ? WHERE delivery_id = ?", 'si', [$status, $deliveryId]);
        $stmt->close();
        return true;
    }

    public function reassignGroundworker(int $deliveryId, int $groundworkerId): bool
    {
        $stmt = $this->run("UPDATE delivery SET groundworker_id = ? WHERE delivery_id = ?", 'ii', [$groundworkerId, $deliveryId]);
        $stmt->close();
        return true;
    }
}