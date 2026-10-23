<?php

require_once __DIR__ . '/../Database/Connection.php';
require_once __DIR__ . '/../Models/GroundWorker.php';

// Groundworker data access: picking someone for a new delivery or
// collection at booking time, the reassignment dropdown, and the
// super admin's add/remove page.
class GroundworkerRepo
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Connection::getConnection();
    }

    // Picks the groundworker with the fewest unfinished jobs, so work
    // is spread out as groundworkers are added. Ties go to the lowest
    // id, which with a single groundworker gives the same result the
    // old "just grab one" version did.
    public function getAnyGroundworker(): ?GroundWorker
    {
        $result = $this->db->query(
            "SELECT g.*
             FROM groundworker g
             ORDER BY (SELECT COUNT(*) FROM delivery d
                       WHERE d.groundworker_id = g.groundworker_id
                         AND d.delivery_status <> 'completed'),
                      g.groundworker_id
             LIMIT 1"
        );
        $row = $result->fetch_assoc();

        if (!$row) {
            return null;
        }

        return new GroundWorker(
            $row['groundworker_id'],
            $row['groundworker_name'],
            $row['groundworker_phone'],
            $row['groundworker_role']
        );
    }

    // Used by the admin delivery/pickup pages to populate the
    // reassignment dropdown - plain arrays, matching the pattern
    // every other admin-facing "getAll" method already uses.
    public function getAllGroundworkers(): array
    {
        $result = $this->db->query("SELECT groundworker_id, groundworker_name FROM groundworker ORDER BY groundworker_name");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Used by the super admin's Groundworkers page.
    // Full list for the management table, with how many pickup and
    // delivery records each person is attached to.
    public function getAllWithJobCounts(): array
    {
        $sql = "SELECT g.groundworker_id, g.groundworker_name,
                       g.groundworker_phone, g.groundworker_role,
                       (SELECT COUNT(*) FROM delivery d
                        WHERE d.groundworker_id = g.groundworker_id) AS job_count
                FROM groundworker g
                ORDER BY g.groundworker_name";

        return $this->db->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    public function countAll(): int
    {
        $row = $this->db->query("SELECT COUNT(*) AS total FROM groundworker")->fetch_assoc();
        return (int)$row['total'];
    }

    public function phoneExists(string $phone): bool
    {
        $stmt = $this->db->prepare("SELECT 1 FROM groundworker WHERE groundworker_phone = ? LIMIT 1");
        $stmt->bind_param('s', $phone);
        $stmt->execute();
        $found = $stmt->get_result()->fetch_assoc() !== null;
        $stmt->close();

        return $found;
    }

    public function exists(int $groundworkerId): bool
    {
        $stmt = $this->db->prepare("SELECT 1 FROM groundworker WHERE groundworker_id = ? LIMIT 1");
        $stmt->bind_param('i', $groundworkerId);
        $stmt->execute();
        $found = $stmt->get_result()->fetch_assoc() !== null;
        $stmt->close();

        return $found;
    }

    public function hasDeliveries(int $groundworkerId): bool
    {
        $stmt = $this->db->prepare("SELECT 1 FROM delivery WHERE groundworker_id = ? LIMIT 1");
        $stmt->bind_param('i', $groundworkerId);
        $stmt->execute();
        $found = $stmt->get_result()->fetch_assoc() !== null;
        $stmt->close();

        return $found;
    }

    public function createGroundworker(string $name, string $phone, string $role): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO groundworker (groundworker_name, groundworker_phone, groundworker_role) VALUES (?, ?, ?)"
        );
        $stmt->bind_param('sss', $name, $phone, $role);
        $stmt->execute();
        $id = $stmt->insert_id;
        $stmt->close();

        return $id;
    }

    public function deleteGroundworker(int $groundworkerId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM groundworker WHERE groundworker_id = ?");
        $stmt->bind_param('i', $groundworkerId);
        $stmt->execute();
        $deleted = $stmt->affected_rows > 0;
        $stmt->close();

        return $deleted;
    }
}