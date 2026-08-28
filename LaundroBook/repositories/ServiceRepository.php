<?php

    require_once __DIR__ . '/../interfaces/ServiceRepositoryInterface.php';
    require_once __DIR__ . '/../models/Service.php';

    class ServiceRepository implements ServiceRepositoryInterface{

        public function getServices(){
            //use the user inserted service to verify with the results from the db


            //dummy db service data
            $known_service = [
                "service_id" => 1, 
                "manager_id" => 1, 
                "wash_type" => "Quick", 
                "load_type" => "Clothes", 
                "price" => 30.00, 
                "duration_minutes" => 25.00, 
                "duration_slots" => 1
            ]; 

            return array(new Service($known_service["service_id"], $known_service["manager_id"],
             $known_service["wash_type"], $known_service["load_type"], $known_service["price"], 
             $known_service["duration_minutes"], $known_service["duration_minutes"]));
        }

        public function updateService(){

        }

        public function deleteService(){

        }
    }