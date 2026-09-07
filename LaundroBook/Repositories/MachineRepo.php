<?php

require_once __DIR__ . '/../Database/Connection.php';
require_once __DIR__ . '/../Interfaces/Repositoryinterfaces.php';

// This class has one job: read and write the machine table. It doesn't
// know anything about slots, bookings, or customers, those all have
// to be cross-referenced with machines somewhere, but that logic lives
// in a service (AvailabilityService).

class MachineRepo implements MachineRepoInterface{
    protected mysqli $db;


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


    // Returns every machine not administratively taken offline. This is
    // what AvailabilityService uses to figure out which machines a
    // customer is even allowed to consider booking.
    //
    // UPDATED REASONING: this deliberately excludes only
    // 'under_maintenance', not 'in_use' - but for a different reason
    // than "the flag doesn't matter." Since AvailabilityService now
    // runs releaseExpiredMachines() before every check, machine_status
    // genuinely IS accurate again (an 'in_use' machine really is busy
    // right now, an 'available' one really isn't). The reason 'in_use'
    // still can't gate this query is structural, not a data-quality
    // problem: machine_status is one global flag with no date
    // attached, while bookings are per-date. A machine correctly
    // showing 'in_use' for its booking today says nothing about
    // whether it's free for a booking next Tuesday - that per-date
    // decision has to come from AvailabilityService cross-referencing
    // real booking rows via getBookedCombosForDate(), which is exactly
    // what happens after this method returns its machine list.
    public function getAvailableMachines(): array{
        $sql = "SELECT machine_id, machine_name, machine_status
                FROM machine
                WHERE machine_status != 'under_maintenance'
                ORDER BY machine_id";

        $result = $this->db->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }


    // Looks up one machine by its ID. Returns null instead of an empty
    // array if nothing matches, so callers can do a simple
    // if ($machine === null) check.
    public function getMachineById(int $machineId): ?array{
        $sql = "SELECT machine_id, machine_name, machine_status
                FROM machine WHERE machine_id = ?";
        $stmt = $this->run($sql, 'i', [$machineId]);
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $result ?: null;
    }


    // Flips a machine's status, e.g. 'available' to 'in_use' once a
    // booking is confirmed, or back to 'available' via
    // AvailabilityService::releaseExpiredMachines() once that
    // booking's slot has genuinely finished. Called by BookingService
    // after a successful insert, by AvailabilityService's release
    // check, and later by the admin panel for manual maintenance
    // toggles.
    //
    //
    // Throws an exception if someone passes a status that isn't one of
    // the three allowed values, instead of silently writing bad data
    // to the database.
    public function updateStatus(int $machineId, string $status):bool{
        $valid = ['available', 'in_use', 'under_maintenance'];
        if(!in_array($status, $valid, true)){
            throw new InvalidArgumentException("Invalid machine status {$status}");
        }

        $sql = "UPDATE machine SET machine_status = ? WHERE machine_id = ?";
        $stmt = $this->run($sql, 'si', [$status, $machineId]);
        $success = $stmt->affected_rows >=0;
        $stmt->close();

        return $success;
    }

    // Quick true/false check for whether a machine_id is real. Used
    // before trying to book or update a machine, so a bad ID fails
    // with a clear error instead of a confusing database problem.
    public function machineExists(int $machineId):bool{
        return $this->getMachineById($machineId) !== null;
    }

}