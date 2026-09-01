<?php

require_once __DIR__ . '/../Database/Connection.php';
require_once __DIR__ . '/../Interfaces/Repositoryinterfaces.php';



//  Single responsibility: read the service table only.
//  Looks up price, duration_minutes, and duration_slots for a given
//  wash_type + load_type combination.
class ServiceRepo implements ServiceRepoInterface
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

    // The main lookup, given a wash type ("quick"/"normal"/"heavy")
    // and a load type ("clothes"/"beddings"/"towels"), finds the
    // matching price and duration. Returns null if the combination
    // doesn't exist, so the caller can show a proper error instead of
    // booking something with no price.
    public function findByType(string $washType, string $loadType): ?array
    {
        $sql = "SELECT service_id, wash_type, load_type, price, duration_minutes, duration_slots
                FROM service
                WHERE wash_type = ? AND load_type = ?";

        $stmt = $this->run($sql, 'ss', [$washType, $loadType]);
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $result ?: null;
    }

    // Looks up a service by its ID directly, for when you already know
    // the service_id (e.g. from an existing booking) instead of the
    // wash/load type combination.
    public function getServiceById(int $serviceId): ?array
    {
        $sql = "SELECT service_id, wash_type, load_type, price, duration_minutes, duration_slots
                FROM service WHERE service_id = ?";

        $stmt = $this->run($sql, 'i', [$serviceId]);
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $result ?: null;
    }
}