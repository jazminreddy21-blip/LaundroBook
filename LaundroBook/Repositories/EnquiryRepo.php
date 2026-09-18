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
}