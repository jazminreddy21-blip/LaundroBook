<?php

require_once __DIR__ . '/../Database/Connection.php';
require_once __DIR__ . '/../Interfaces/Repositoryinterfaces.php';

class BookingRepo implements BookingRepoInterface{
    private mysqli $db;

    public function __construct()
    {
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
    // row happens to exist, function is not yet used.
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


    //I assume that a function such as getToday's booking will be
    //in this repo and that includes pending bookings
    public function todaysBookings(): int
    {
        //this query is minimal so it simply returns the number, not
        //the actual bookings
        $sql = "SELECT COUNT(*) as total FROM booking WHERE booking_date = ?";

        $stmt = $this->run($sql, 's', [date('Y-m-d')]);
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close(); 

        return (int)($result['total'] ?? 0); 
    } 

    // ------------------------------------------------------------
    // Everything below this line was added to support the admin
    // dashboard stats, notifications and the Booking Management page.

    // Count of bookings still awaiting admin approval, shown on the
    // "Pending Bookings" stat card.
    public function pendingBookingsCount(): int
    {
        $sql = "SELECT COUNT(*) as total FROM booking WHERE LOWER(status) = 'pending'";
        $result = $this->db->query($sql)->fetch_assoc();
        return (int)($result['total'] ?? 0);
    }

    // Sum of total_price for bookings made today. There is no
    // payments table in the schema yet, so "today's revenue" is
    // derived from today's bookings instead. Cancelled bookings are
    // excluded since no money is actually collected for those.
    public function todaysRevenue(): float
    {
        $sql = "SELECT COALESCE(SUM(total_price), 0) as total
                FROM booking
                WHERE booking_date = ? AND LOWER(status) != 'cancelled'";

        $stmt = $this->run($sql, 's', [date('Y-m-d')]);
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return (float)($result['total'] ?? 0);
    }

    // Same idea as todaysRevenue()/countByStatusBetween(), but for an
    // arbitrary date range. Used by the Reports page.
    public function revenueBetween(string $startDate, string $endDate): float
    {
        $sql = "SELECT COALESCE(SUM(total_price), 0) as total
                FROM booking
                WHERE booking_date BETWEEN ? AND ? AND LOWER(status) != 'cancelled'";

        $stmt = $this->run($sql, 'ss', [$startDate, $endDate]);
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return (float)($result['total'] ?? 0);
    }

    public function countByStatusBetween(string $status, string $startDate, string $endDate): int
    {
        $sql = "SELECT COUNT(*) as total
                FROM booking
                WHERE LOWER(status) = LOWER(?) AND booking_date BETWEEN ? AND ?";

        $stmt = $this->run($sql, 'sss', [$status, $startDate, $endDate]);
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return (int)($result['total'] ?? 0);
    }

    // Full booking list for the Booking Management page, joined with
    // customer/machine/service/slot so the admin table can show names
    // instead of raw IDs. Supports the same three filters the page's
    // search form offers: free-text (reference or customer name),
    // status, and an exact booking date.
    public function getAllBookings(array $filters = []): array
    {
        $sql = "SELECT b.booking_id, b.booking_reference, b.booking_date, b.total_price, b.status,
                       c.customer_name, c.customer_email, c.customer_phone,
                       m.machine_name,
                       sv.wash_type, sv.load_type,
                       sl.slot_label
                FROM booking b
                JOIN customer c ON c.customer_id = b.customer_id
                JOIN machine m ON m.machine_id = b.machine_id
                JOIN service sv ON sv.service_id = b.service_id
                JOIN slot sl ON sl.slot_id = b.slot_id
                WHERE 1=1";

        $types = '';
        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (b.booking_reference LIKE ? OR c.customer_name LIKE ?)";
            $like = '%' . $filters['search'] . '%';
            $types .= 'ss';
            $params[] = $like;
            $params[] = $like;
        }

        if (!empty($filters['status'])) {
            $sql .= " AND LOWER(b.status) = LOWER(?)";
            $types .= 's';
            $params[] = $filters['status'];
        }

        if (!empty($filters['date'])) {
            $sql .= " AND b.booking_date = ?";
            $types .= 's';
            $params[] = $filters['date'];
        }

        $sql .= " ORDER BY b.booking_date DESC, b.booking_id DESC";

        $stmt = $this->run($sql, $types, $params);
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $result;
    }

    // Looks up one booking (with the same joined display data as
    // getAllBookings()) by its human-facing reference, e.g. LB-00001.
    // Used by the "Manage" action on the Booking Management page.
    public function findBookingByReference(string $reference): ?array
    {
        $sql = "SELECT b.booking_id, b.booking_reference, b.booking_date, b.total_price, b.status,
                       b.machine_id,
                       c.customer_name, c.customer_email, c.customer_phone,
                       m.machine_name,
                       sv.wash_type, sv.load_type,
                       sl.slot_label
                FROM booking b
                JOIN customer c ON c.customer_id = b.customer_id
                JOIN machine m ON m.machine_id = b.machine_id
                JOIN service sv ON sv.service_id = b.service_id
                JOIN slot sl ON sl.slot_id = b.slot_id
                WHERE b.booking_reference = ?";

        $stmt = $this->run($sql, 's', [$reference]);
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $result ?: null;
    }

    // Updates a booking's status. Only the four values the CHECK
    // constraint allows are accepted; anything else throws instead of
    // silently failing the UPDATE.
    public function updateStatus(int $bookingId, string $status): bool
    {
        $valid = ['pending', 'in_progress', 'completed', 'cancelled'];
        if (!in_array(strtolower($status), $valid, true)) {
            throw new InvalidArgumentException("Invalid booking status {$status}");
        }

        $sql = "UPDATE booking SET status = ? WHERE booking_id = ?";
        $stmt = $this->run($sql, 'si', [strtolower($status), $bookingId]);
        $success = $stmt->affected_rows >= 0;
        $stmt->close();

        return $success;
    }
}