<?php

    interface BookingRepositoryInterface{
        //add booking
        public function createBooking($customer_id, $machine_id, $slot_id,$service_id, $manager_id,$total_price);
        //find booking
        public function findBooking($booking_id); 
        //update booking
        public function updateBooking($booking_id); 

        //delete booking
        public function deleteBooking($booking_id); 
    }