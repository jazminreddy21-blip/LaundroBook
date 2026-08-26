<?php
    //for testing purposes we won't specify the return type for the method here

    interface SlotRepositoryInterface{
        public function findAvailableSlot(int $SlotId); 
        public function returnAvailableSlots(); //collection type
        //pass a s 
    }