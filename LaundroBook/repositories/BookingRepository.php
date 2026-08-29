<?php
    require_once __DIR__ . '/../interfaces/BookingRepositoryInterface.php';
    require_once __DIR__ . '/../models/Booking.php'; 

    class BookingRepository implements BookingRepositoryInterface{
        private static $fakeIdCounter = 1;
        public function createBooking($customer_id, $machine_id, $slot_id, $service_id, $manager_id, $total_price){
            //the booking object is created here using params:
            //dummy booking data is used here in place of a db
            $newBookingId = self::$fakeIdCounter;
            self::$fakeIdCounter++;

            // (Between 0 and the maximum number your system supports)
            $booking_reference = rand();
            
            $booking_date = new DateTime('now'); //db uses a timestamp
            
            $machine_free_at = new DateTimeImmutable('09:30:00'); 

            $status = "Active"; 

            //insert and return the new booking object

            return new Booking($newBookingId, $customer_id, $machine_id, 
                $slot_id, $service_id, $manager_id, $booking_reference, 
                $booking_date, $machine_free_at, $total_price, $status
            ); 
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