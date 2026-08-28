<?php
    require_once __DIR__ . '/../interfaces/SystemManagerRepositoryInterface.php'; 
    require_once __DIR__ . '/../models/SystemManager.php';

    class SystemMangerRepository implements SystemManagerRepositoryInterface{
        //technically, this repo only allows for read purposes, no delete or create

        public function getSystemManager(){

            $known_manager = [
                "manager_id" => 1, 
                "username" => "Jones", 
                "password_hash" => "5678*&^%%hhkdg"
            ]; 

            return new SystemManager($known_manager["manager_id"], $known_manager["Jones"], $known_manager["password_hash"]); 

        }

        //this probably needs to be a private method or something hidden and exclusive 
        //to the manager or admin
        public function updateManager(){

        }

    } 