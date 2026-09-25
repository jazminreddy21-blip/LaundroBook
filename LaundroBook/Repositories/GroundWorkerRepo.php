<?php

require_once __DIR__ . '/../Database/Connection.php';
require_once __DIR__ . '/../Models/GroundWorker.php';

// Deliberately minimal - only what BookingService needs to assign a
// groundworker to a new delivery/collection at booking time. Same
// "just grab whichever exists" reasoning already used for
// getPrimaryManager() elsewhere in this project - there's no
// assignment logic (workload, availability, area) built yet, so this
// is a placeholder to keep, matching the current scale of the system.
class GroundworkerRepo
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Connection::getConnection();
    }

    public function getAnyGroundworker(): ?GroundWorker
    {
        $result = $this->db->query("SELECT * FROM groundworker LIMIT 1");
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
}