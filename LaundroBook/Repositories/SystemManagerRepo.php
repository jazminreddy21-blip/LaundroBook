<?php
    require_once __DIR__ . '/../Interfaces/Repositoryinterfaces.php'; 
    require_once __DIR__ . '/../Models/SystemManager.php'; 
    require_once __DIR__ . '/../Database/Connection.php';
    //mocking the db for the time being

    class SystemManagerRepo implements SystemManagerRepoInterface{
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

        public function findManager(string $username): ?SystemManager{
            
            $sql = "SELECT manager_id, manager_username, password_hash
                    FROM system_manager
                    WHERE manager_username = ?";

            $stmt = $this->run($sql, 's', [$username]); 
            $result = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if(!$result){
                return null;
            }

            return new SystemManager(
                $result['manager_id'], 
                $result['manager_username'], 
                $result['password_hash']
            );
        }

        // Whichever admin currently has the lowest manager_id is the
        // super admin - computed fresh here rather than stored anywhere,
        // so if that original account is ever deleted, the next-oldest
        // remaining admin automatically becomes the super admin on
        // their next login.
        public function isSuperAdmin(int $managerId): bool{
            $result = $this->db->query("SELECT MIN(manager_id) AS lowest_id FROM system_manager")->fetch_assoc();
            return $managerId === (int)$result['lowest_id'];
        }

        // Used by the super admin's "Add Admin" feature. Every admin
        // created this way has identical features to any other admin.
        public function createManager(string $username, string $passwordHash): int{
            $sql = "INSERT INTO system_manager (manager_username, password_hash) VALUES (?, ?)";
            $stmt = $this->run($sql, 'ss', [$username, $passwordHash]);
            $newId = $stmt->insert_id;
            $stmt->close();
            return $newId;
        }

        // Used by the "Manage Admins" list on adminRegister.php.
        public function getAllManagers(): array{
            $result = $this->db->query("SELECT manager_id, manager_username FROM system_manager ORDER BY manager_id");
            return $result->fetch_all(MYSQLI_ASSOC);
        }

        public function countAll(): int{
            $result = $this->db->query("SELECT COUNT(*) AS total FROM system_manager")->fetch_assoc();
            return (int)$result['total'];
        }

        public function hasAssociatedRecords(int $managerId): bool{
            $tables = ['booking', 'machine', 'service', 'slot', 'enquiry'];
            foreach ($tables as $table) {
                $stmt = $this->run("SELECT 1 FROM {$table} WHERE manager_id = ? LIMIT 1", 'i', [$managerId]);
                $found = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                if ($found) {
                    return true;
                }
            }
            return false;
        }

        public function deleteManager(int $managerId): bool{
            $stmt = $this->run("DELETE FROM system_manager WHERE manager_id = ?", 'i', [$managerId]);
            $stmt->close();
            return true;
        }
    }