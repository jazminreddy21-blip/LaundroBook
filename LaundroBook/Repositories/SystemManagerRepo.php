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

        public function findManager($username): ?SystemManager{
            
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
    }