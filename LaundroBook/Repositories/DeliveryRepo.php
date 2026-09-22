<?php

require_once __DIR__ . '/../Database/Connection.php';
require_once __DIR__ . '/../Interfaces/Repositoryinterfaces.php';

// This class has one job: read and write the `delivery` table.

class DeliveryRepo implements DeliveryRepoInterface{
    private mysqli $db;

    public function __construct(){
        $this->db = Connection::getConnection();
    }

    private function run(string $sql, string $types = '', array $params = []): mysqli_stmt{
        $stmt = $this->db->prepare($sql);
        if($types !== ''){
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt;
    }

    public function todaysCountByType(string $type): int{
        $sql = "SELECT COUNT(*) as total
                FROM delivery
                WHERE delivery_type = ? AND DATE(scheduled_time) = CURDATE()";

        $stmt = $this->run($sql, 's', [$type]);
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return (int)($result['total'] ?? 0);
    }

    // Full list for the Pickup/Delivery Management pages, joined with
    // booking/customer/groundworker so the table can show names
    // instead of raw IDs. $filters supports 'type' (collection /
    // delivery - each page fixes this so admins never mix the two),
    // 'status' and free-text 'search' on the customer name or booking
    // reference.
    public function getAll(array $filters = []): array{
        $sql = "SELECT d.delivery_id, d.booking_id, d.delivery_type, d.delivery_status, d.scheduled_time,
                       b.booking_reference,
                       c.customer_name, c.customer_phone, c.address,
                       g.groundworker_name, g.groundworker_phone
                FROM delivery d
                JOIN booking b ON b.booking_id = d.booking_id
                JOIN customer c ON c.customer_id = b.customer_id
                JOIN groundworker g ON g.groundworker_id = d.groundworker_id
                WHERE 1=1";

        $types = '';
        $params = [];

        if (!empty($filters['type'])) {
            $sql .= " AND d.delivery_type = ?";
            $types .= 's';
            $params[] = $filters['type'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND LOWER(d.delivery_status) = LOWER(?)";
            $types .= 's';
            $params[] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (b.booking_reference LIKE ? OR c.customer_name LIKE ?)";
            $like = '%' . $filters['search'] . '%';
            $types .= 'ss';
            $params[] = $like;
            $params[] = $like;
        }

        $sql .= " ORDER BY d.scheduled_time DESC";

        $stmt = $this->run($sql, $types, $params);
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $result;
    }

    public function findById(int $deliveryId): ?array{
        $sql = "SELECT d.delivery_id, d.booking_id, d.delivery_type, d.delivery_status, d.scheduled_time,
                       b.booking_reference,
                       c.customer_name, c.customer_phone, c.address,
                       g.groundworker_name, g.groundworker_phone
                FROM delivery d
                JOIN booking b ON b.booking_id = d.booking_id
                JOIN customer c ON c.customer_id = b.customer_id
                JOIN groundworker g ON g.groundworker_id = d.groundworker_id
                WHERE d.delivery_id = ?";

        $stmt = $this->run($sql, 'i', [$deliveryId]);
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $result ?: null;
    }

    // Only the three statuses the CHECK constraint allows are accepted.
    public function updateStatus(int $deliveryId, string $status): bool{
        $valid = ['pending', 'in_progress', 'completed'];
        if(!in_array(strtolower($status), $valid, true)){
            throw new InvalidArgumentException("Invalid delivery status {$status}");
        }

        $sql = "UPDATE delivery SET delivery_status = ? WHERE delivery_id = ?";
        $stmt = $this->run($sql, 'si', [strtolower($status), $deliveryId]);
        $success = $stmt->affected_rows >= 0;
        $stmt->close();

        return $success;
    }
}
