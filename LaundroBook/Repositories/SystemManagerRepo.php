<?php
    require_once __DIR__ . '/../Interfaces/Repositoryinterfaces.php'; 
    require_once __DIR__ . '/../Models/SystemManager.php'; 

    //mocking the db for the time being

    class SystemManagerRepo implements SystemManagerRepoInterface{

        public function findManager($username): ?SystemManager{
            
            $known_manager = [
                "manager_id" => 1, 
                "username" => "Mabutho", 
                "password_hash" => password_hash("Tpnidy3355$", PASSWORD_DEFAULT)
            ];
            
            if($username == $known_manager["username"]){
                return new SystemManager($known_manager["manager_id"],
                 $known_manager["username"],
                  $known_manager["password_hash"]); 
            }
            else{
                return null; 
            }
        }
    }