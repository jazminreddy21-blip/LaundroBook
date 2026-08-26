<?php

    require_once __DIR__ . '/../interfaces/MachineRepositoryInterface.php'; 

    class MachineRepository implements MachineRepositoryInterface{

        public function findAvailableMachine($MachineId){
            $knownMachines = [
                1 => true,
                2 => false,
                3 => true,
            ];

            return isset($knownMachines[$MachineId]) ? $knownMachines[$MachineId] : false;
        }
        public function returnAvailableMachines(){
            $availableMachines = array("Machine 1", "Machine 2");
            
            return $availableMachines; 
        }

    }