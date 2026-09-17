<?php
    require_once __DIR__ . '/../Interfaces/Repositoryinterfaces.php'; 
    require_once __DIR__ . '/../Models/SystemManager.php'; 


    class SystemManagerRepo implements SystemManagerRepoInterface{

        public function findManager(){
            $known_manager = [
                "manager_id" => 1, 
                "username" => "Mabutho", 
                "password_hash" => "&^%^&*554363748^%#*6"
            ];

            return new SystemManager($known_manager["manager_id"], $known_manager["username"], $known_manager["password_hash"]); 
        }
    }