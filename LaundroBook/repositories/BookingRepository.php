<?php


    class BookingRepository implements BookingRepositoryInterface{
        public function createBooking(){
            //the booking object is created here using:
            //1. booking_id (auto-gen by the database)
            //2. customer_id (retrieved from the customer object)
            //3. machine_id (retrieved from the machine object)
            //4. slot_id (retrieved from the slot object)
            //5. manager_id (retrieved from the manager object)
            //6. booking reference (auto-generated here)
            //7. booking date (user inserts this)
            //8. other fields 

            
        
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