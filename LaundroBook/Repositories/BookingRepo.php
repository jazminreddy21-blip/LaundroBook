<?php

require_once __DIR__ . '/../Database/Connection.php';
require_once __DIR__ . '/../Interfaces/Repositoryinterfaces.php';


class BookingRepo implements BookingRepoInterface{
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

    public function insert(int $customerId, int $managerId, array $service, array $data): int
    {
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
 
        $bookingId = $stmt->insert_id;
        $stmt->close();
 
        // booking_reference needs the real booking_id, which only exists
        // after the insert - so it's set in a quick follow-up update.
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

    // Used by AvailabilityService to know which machine/slot pairs are
    // already taken on a given date. Excludes cancelled bookings.
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
 
    public function getPrimaryManager(): array
    {
        $result = $this->db->query("SELECT manager_id FROM system_manager LIMIT 1");
        return $result->fetch_assoc();
    }
 
    public function findBooking(int $bookingId): ?array
    {
        $sql = "SELECT * FROM booking WHERE booking_id = ?";
 
        $stmt = $this->run($sql, 'i', [$bookingId]);
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
 
        return $result ?: null;
    }

    // Finds every still-Pending booking whose slot has genuinely
    // finished, so AvailabilityService can flip the machine back to
    // available. A Heavy Wash books two consecutive slots as two
    // separate rows sharing the same customer/machine/date/service -
    // the NOT EXISTS check below makes sure a row only releases once
    // BOTH rows are done, not just the first one.
    public function getBookingsPastEndTime(): array
    {
        $sql = "SELECT b.booking_id, b.machine_id
                FROM booking b
                JOIN slot s ON b.slot_id = s.slot_id
                WHERE b.status = 'Pending'
                  AND CONCAT(b.booking_date, ' ', TIME(s.end_time)) <= NOW()
                  AND NOT EXISTS (
                      SELECT 1
                      FROM booking b2
                      JOIN slot s2 ON b2.slot_id = s2.slot_id
                      WHERE b2.customer_id = b.customer_id
                        AND b2.machine_id = b.machine_id
                        AND b2.booking_date = b.booking_date
                        AND b2.service_id = b.service_id
                        AND b2.status = 'Pending'
                        AND CONCAT(b2.booking_date, ' ', TIME(s2.end_time)) > NOW()
                  )";

        $result = $this->db->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function markCompleted(int $bookingId): bool
    {
        $stmt = $this->run("UPDATE booking SET status = 'completed' WHERE booking_id = ?", 'i', [$bookingId]);
        $stmt->close();
        return true;
    }

    // Admin dashboard stat card
    public function todaysBookings(): int
    {
        $sql = "SELECT COUNT(*) AS total FROM (
                    SELECT 1
                    FROM booking
                    WHERE booking_date = CURDATE()
                    GROUP BY customer_id, machine_id, booking_date, service_id
                ) AS grouped";
        $result = $this->db->query($sql)->fetch_assoc();
        return (int)$result['total'];
    }

    // Admin dashboard stat card
    public function pendingBookingsCount(): int
    {
        $sql = "SELECT COUNT(*) AS total FROM (
                    SELECT 1
                    FROM booking
                    WHERE status = 'pending'
                    GROUP BY customer_id, machine_id, booking_date, service_id
                ) AS grouped";
        $result = $this->db->query($sql)->fetch_assoc();
        return (int)$result['total'];
    }

    // Admin dashboard stat card
    public function todaysRevenue(): float
    {
        $sql = "SELECT COALESCE(SUM(group_total), 0) AS revenue FROM (
                    SELECT MAX(total_price) AS group_total
                    FROM booking
                    WHERE booking_date = CURDATE() AND status != 'cancelled'
                    GROUP BY customer_id, machine_id, booking_date, service_id
                ) AS grouped";
        $result = $this->db->query($sql)->fetch_assoc();
        return (float)$result['revenue'];
    }

    // Booking Management page - joins in the columns the admin table
    // actually shows per row, so no second lookup is needed per booking.
    //
    // FIXED: previously returned one raw row per booking row, meaning
    // a Heavy Wash booking (two rows sharing the same customer,
    // machine, date, and service - the same grouping key already
    // proven in getBookingsPastEndTime()) showed up as two entirely
    // separate entries in the admin table, each with its own
    // reference, looking like two unrelated bookings. Now grouped by
    // that same key: slot_label combines both times, total_price uses
    // MAX rather than SUM (both rows store the FULL price, not a
    // split amount, so summing would double it), and MIN(booking_id)
    // picks one consistent representative row - the earlier-created
    // one - whose reference and booking_id the admin table's action
    // form uses. A standard single-slot booking has no sibling row to
    // group with, so GROUP BY has no visible effect on it at all.
    public function getAllBookings(array $filters = []): array
    {
        $sql = "SELECT MIN(b.booking_id) AS booking_id,
                       MIN(b.booking_reference) AS booking_reference,
                       b.booking_date,
                       MAX(b.status) AS status,
                       MAX(b.total_price) AS total_price,
                       c.customer_name,
                       m.machine_name,
                       GROUP_CONCAT(s.slot_label ORDER BY s.start_time SEPARATOR ' & ') AS slot_label,
                       sv.wash_type, sv.load_type
                FROM booking b
                JOIN customer c ON b.customer_id = c.customer_id
                JOIN machine m ON b.machine_id = m.machine_id
                JOIN slot s ON b.slot_id = s.slot_id
                JOIN service sv ON b.service_id = sv.service_id
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
            $sql .= " AND b.status = ?";
            $types .= 's';
            $params[] = $filters['status'];
        }

        if (!empty($filters['date'])) {
            $sql .= " AND b.booking_date = ?";
            $types .= 's';
            $params[] = $filters['date'];
        }

        $sql .= " GROUP BY b.customer_id, b.machine_id, b.booking_date, b.service_id
                  ORDER BY b.booking_date DESC, booking_id DESC";

        $stmt = $this->run($sql, $types, $params);
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function findBookingByReference(string $reference): ?array
    {
        $sql = "SELECT * FROM booking WHERE booking_reference = ?";
        $stmt = $this->run($sql, 's', [$reference]);
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result ?: null;
    }

    // Admin's manual status change on the Booking Management page -
    // different from markCompleted() above, which is the automatic
    // "slot has ended" completion path.
    public function updateStatus(int $bookingId, string $status): bool
    {
        $stmt = $this->run("UPDATE booking SET status = ? WHERE booking_id = ?", 'si', [$status, $bookingId]);
        $stmt->close();
        return true;
    }

    // Updates every booking row sharing the same customer+machine+date
    // +service as $bookingId - the same grouping key used everywhere
    // else a Heavy Wash's two rows need to be treated as one logical
    // booking. Without this, updating a Heavy Wash's status via
    // updateStatus() above would only change ONE of its two rows,
    // leaving the pair out of sync (one 'completed', the other still
    // 'pending') even though the admin table now displays them as one
    // combined entry. For a standard single-slot booking, this
    // behaves identically to updateStatus() above, since it has no
    // sibling row to find.
    public function updateStatusForGroup(int $bookingId, string $status): bool
    {
        $groupRow = $this->findBooking($bookingId);
        if ($groupRow === null) {
            return false;
        }

        $sql = "UPDATE booking
                SET status = ?
                WHERE customer_id = ? AND machine_id = ? AND booking_date = ? AND service_id = ?";
        $stmt = $this->run($sql, 'siisi', [
            $status,
            (int)$groupRow['customer_id'],
            (int)$groupRow['machine_id'],
            $groupRow['booking_date'],
            (int)$groupRow['service_id'],
        ]);
        $stmt->close();
        return true;
    }

    // Reports page
    // Reports page - same double-counting fix as todaysRevenue() above.
    public function revenueBetween(string $startDate, string $endDate): float
    {
        $sql = "SELECT COALESCE(SUM(group_total), 0) AS revenue FROM (
                    SELECT MAX(total_price) AS group_total
                    FROM booking
                    WHERE booking_date BETWEEN ? AND ? AND status != 'cancelled'
                    GROUP BY customer_id, machine_id, booking_date, service_id
                ) AS grouped";
        $stmt = $this->run($sql, 'ss', [$startDate, $endDate]);
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return (float)$result['revenue'];
    }

    // Reports page
    // FIXED: same double-counting bug - used by the Reports page for
    // "Completed" and "Cancelled" booking counts, which were similarly
    // inflated for every Heavy Wash booking.
    public function countByStatusBetween(string $status, string $startDate, string $endDate): int
    {
        $sql = "SELECT COUNT(*) AS total FROM (
                    SELECT 1
                    FROM booking
                    WHERE status = ? AND booking_date BETWEEN ? AND ?
                    GROUP BY customer_id, machine_id, booking_date, service_id
                ) AS grouped";
        $stmt = $this->run($sql, 'sss', [$status, $startDate, $endDate]);
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return (int)$result['total'];
    }

}