<?php

    interface BookingRepositoryInterface{
        //add booking
        public function createBooking();
        //find booking
        public function findBooking($booking_id); 
        //update booking
        public function updateBooking($booking_id); 

        //delete booking
        public function deleteBooking($booking_id); 
    }