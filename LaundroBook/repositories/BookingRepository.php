<?php


    class BookingRepository implements BookingRepositoryInterface{
        public function createBooking($customer_id, $machine_id, $slot_id, $manager_id,
         $booking_date, $machine_free_at, $total_price, $status){
            //the booking object is created here using params:

            
        
        }
        //find booking
        public function findBooking($booking_id){

        }
        //update booking
        public function updateBooking($booking_id){

        } 

        //delete booking
        public function deleteBooking($booking_id){}
    }