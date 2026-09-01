<?php

require_once __DIR__ . '/../Database/Connection.php';
require_once __DIR__ . '/../Interfaces/Repositoryinterfaces.php';

// This class has one job: read the slot table. It doesn't know
// anything about machines, bookings, or customers, cross-referencing
// slots against those belongs in AvailabilityService.

class SlotRepo implements SlotRepoInterface{
    private mysqli $db;

    public function __construct(){
        $this->db = Connection::getConnection();
    }

    public function run(string $sql, string $types = '', array $params = []): mysqli_stmt{
        $stmt = $this->db->prepare($sql);
        if($types !== ''){
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt;
    }

    // Returns every slot the manager hasn't deactivated, ordered by
    // start_time. That ordering matters, AvailabilityService relies
    // on the slots coming back in time order to figure out which slot
    // comes "next" when checking Heavy Wash bookings (which need two
    // slots in a row).
    public function getActiveSlots(): array{
        $sql = "SELECT slot_id, slot_label, start_time, end_time, is_active
                FROM slot
                WHERE is_active = 1
                ORDER BY start_time";
        $result = $this->db->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Looks up one slot by its ID. Returns null instead of an empty
    // array if nothing matches, so callers can do a simple
    // if ($slot === null) check.
    public function getSlotById(int $slotId): ?array{
        $sql = "SELECT slot_id, slot_label, start_time, end_time, is_active 
                FROM slot WHERE slot_id = ?";
        
        $stmt = $this->run($sql, 'i', [$slotId]);
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $result ?: null;
    }

}