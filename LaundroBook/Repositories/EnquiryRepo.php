<?php

require_once __DIR__ . '/../Database/Connection.php';
require_once __DIR__ . '/../Interfaces/Repositoryinterfaces.php';

class EnquiryRepo implements EnquiryRepoInterface
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

    // enquiry.status has no CHECK constraint (unlike booking.status),
    // so 'Pending' here is just a starting value to match, not
    // something the database enforces.
    public function insert(string $name, string $email, string $message): int
    {
        $manager = $this->getPrimaryManager();

        $sql = "INSERT INTO enquiry (manager_id, name, email, message, status)
                VALUES (?, ?, ?, ?, 'Pending')";

        $stmt = $this->run($sql, 'isss', [
            (int)$manager['manager_id'],
            $name,
            $email,
            $message,
        ]);

        $newId = $stmt->insert_id;
        $stmt->close();

        return $newId;
    }

    // Same reasoning as BookingRepo::getPrimaryManager() - there is
    // only one manager in the system right now, so this just grabs
    // whichever row exists.
    private function getPrimaryManager(): array
    {
        $result = $this->db->query("SELECT manager_id FROM system_manager LIMIT 1");
        return $result->fetch_assoc();
    }

    // Used by the admin Enquiry Management page. $search matches
    // against name, email, or the combined message text.
    public function getAll(string $search = ''): array
    {
        $sql = "SELECT enquiry_id, name, email, message, date_submitted, status
                FROM enquiry
                WHERE 1=1";

        $types = '';
        $params = [];

        if ($search !== '') {
            $sql .= " AND (name LIKE ? OR email LIKE ? OR message LIKE ?)";
            $like = '%' . $search . '%';
            $types = 'sss';
            $params = [$like, $like, $like];
        }

        $sql .= " ORDER BY date_submitted DESC";

        $stmt = $this->run($sql, $types, $params);
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Used by DashboardService for the "pending enquiries" system
    // notification.
    public function countByStatus(string $status): int
    {
        $result = $this->run("SELECT COUNT(*) AS total FROM enquiry WHERE status = ?", 's', [$status])
            ->get_result()->fetch_assoc();
        return (int)$result['total'];
    }

    // Only Pending/Responded are accepted here, in code - enquiry.status
    // has no CHECK constraint enforcing this at the database level,
    // unlike booking.status or delivery.delivery_status.
    public function updateStatus(int $enquiryId, string $status): bool
    {
        $valid = ['Pending', 'Responded'];
        if (!in_array($status, $valid, true)) {
            throw new InvalidArgumentException("Invalid enquiry status {$status}");
        }

        $stmt = $this->run("UPDATE enquiry SET status = ? WHERE enquiry_id = ?", 'si', [$status, $enquiryId]);
        $stmt->close();
        return true;
    }
}