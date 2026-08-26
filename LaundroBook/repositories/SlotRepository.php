<?php
    require_once __DIR__ . '/../interfaces/SlotRepositoryInterface.php';

    class SlotRepository implements SlotRepositoryInterface{

        //this will contain the implementation and CRUD operations
        public function findAvailableSlot($SlotId){
            // TEMPORARY — hardcoded until DB connection exists
            //once the db arrives, we'll straight up return objects of type Slot
            $knownSlots = [
                1 => true,
                2 => false,
                3 => true,
            ];

            return isset($knownSlots[$SlotId]) ? $knownSlots[$SlotId] : false;
        }

        //second dummy method that simply returns all available slots
        //a similar method will be implemented in the machine repo
        public function returnAvailableSlots(){
            //database doesn't exist yet, so it's just hardcoded data
            //in an array
            $available_slots = array("08:00-08:45", "08:45-09:15", "09:15-10:15");
            
            return $available_slots; 
        }
    }