<?php

require_once __DIR__ . '/../Database/Connection.php';
require_once __DIR__ . '/../Interfaces/Repositoryinterfaces.php';

class BookingRepo implements BookingRepoInterface{
    private mysqli $db;

    public function __construct()
    {
        // Grabs the one shared database connection instead of opening a
        // new one every time a BookingRepo gets created.
        $this->db = Connection::getConnection();
    }

    // Small helper so every method below doesn't repeat the same
    // prepare -> bind -> execute steps by hand. $types is mysqli's
    // bind_param type string (e.g. 'i' for one int, 'ss' for two
    // strings) and must have one letter per value in $params, in order.
    private function run(string $sql, string $types = '', array $params = []): mysqli_stmt
    {
        $stmt = $this->db->prepare($sql);
        if ($types !== '') {
            $stmt->bind_param($types, ...$params); //uses spread/splat operator '...' to unpack array into individual arguments
        }
        $stmt->execute();
        return $stmt;
    }

    // Creates a new booking row. Status always starts as 'Pending',
    // nothing here decides otherwise. Returns the new booking's ID so
    // whoever called this can generate a reference number, insert a
    // second row for Heavy Wash, etc.
    public function insert(int $customerId, int $managerId, array $service, array $data): int
    {
        // Reference gets written properly further down, this is just
        // a placeholder so the column isn't left blank while we insert.
        $placeholderRef = 'PENDING';
 
        $sql = "INSERT INTO booking
                (customer_id, manager_id, machine_id, slot_id, service_id, booking_reference, booking_date, total_price, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending')";
 
        $stmt = $this->run($sql, 'iiiiissd', [
            $customerId,
            $managerId,
            (int)$data['machine_id'],
            (int)$data['slot_id'],
            (int)$service['service_id'],
            $placeholderRef,
            $data['booking_date'],
            (float)$service['price'],
        ]);
 
        // insert_id is the auto-increment value MySQL just generated
        // for this row, this is how we find out the new booking_id.
        $bookingId = $stmt->insert_id;
        $stmt->close();
 
        // The reference (e.g. LB-00001) needs the real booking_id,
        // which only exists after the row is inserted, so it can't be
        // included in the first INSERT above. This runs a quick second
        // query right after to fill it in.
        $reference = 'LB-' . str_pad((string)$bookingId, 5, '0', STR_PAD_LEFT);
        $this->updateReference($bookingId, $reference);
 
        return $bookingId;
    }

    // Fills in the real booking_reference after insert(). Kept private
    // since nothing outside this class should ever need to update a
    // reference on its own, it's only ever a follow-up step to insert().
    private function updateReference(int $bookingId, string $reference): void
    {
        $sql = "UPDATE booking SET booking_reference = ? WHERE booking_id = ?";
        $stmt = $this->run($sql, 'si', [$reference, $bookingId]);
        $stmt->close();
    }

    // Returns which machine/slot combinations are already booked on a
    // given date, so AvailabilityService can figure out what's still
    // free. Cancelled bookings don't count as taken, since that slot
    // is effectively open again.
    public function getBookedCombosForDate(string $bookingDate): array
    {
        $sql = "SELECT machine_id, slot_id
                FROM booking
                WHERE booking_date = ? AND status != 'cancelled'";
 
        $stmt = $this->run($sql, 's', [$bookingDate]);
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
 
        return $result;
    }
 
    // Every booking needs a manager_id, assuming that there is one
    // manager in the system right now, so this just grabs whichever
    // row happens to exist.
    public function getPrimaryManager(): array
    {
        $result = $this->db->query("SELECT manager_id FROM system_manager LIMIT 1");
        return $result->fetch_assoc();
    }
 
    // Looks up a single booking by its ID. Returns null instead of an
    // empty array if nothing matches, so callers can do a simple
    // if ($booking === null) check.
    public function findBooking(int $bookingId): ?array
    {
        $sql = "SELECT * FROM booking WHERE booking_id = ?";
 
        $stmt = $this->run($sql, 'i', [$bookingId]);
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
 
        return $result ?: null;
    }

    // This is the actual fix for the Polling-Based Machine Release
    // weakness (Analysis Phase Section 3.5). It finds every still-
    // "Pending" booking whose slot has genuinely finished, so the
    // caller (AvailabilityService) can flip the machine back to
    // available and mark the booking completed.
    //
    // The date reconstruction matters: slot.end_time only stores a
    // time-of-day, not which date it applies to - a slot labelled
    // "09:30 - 10:15" is reused every day. Comparing it against NOW()
    // directly would be meaningless for a booking on a different date
    // than today. CONCAT(b.booking_date, ' ', TIME(s.end_time)) builds
    // the real moment this specific booking actually ends, then
    // compares THAT against NOW() - not the bare slot time on its own.
    public function getBookingsPastEndTime(): array
    {
        $sql = "SELECT b.booking_id, b.machine_id
                FROM booking b
                JOIN slot s ON b.slot_id = s.slot_id
                WHERE b.status = 'Pending'
                  AND CONCAT(b.booking_date, ' ', TIME(s.end_time)) <= NOW()";

        $result = $this->db->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Marks a booking completed once its slot has ended - called
    // alongside flipping the machine back to available, so the two
    // stay in sync with each other.
    public function markCompleted(int $bookingId): bool
    {
        $sql = "UPDATE booking SET status = 'completed' WHERE booking_id = ?";
        $stmt = $this->run($sql, 'i', [$bookingId]);
        $success = $stmt->affected_rows >= 0;
        $stmt->close();
        return $success;
    }

}