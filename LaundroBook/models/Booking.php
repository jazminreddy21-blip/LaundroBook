<?php

    class Booking{
        private $booking_id; 
        private $customer_id; 
        private $machine_id; 
        private $slot_id; 
        private $service_id; 
        private $manager_id; 
        private $booking_reference; 
        private $bookingDate; 
        private $machine_free_at; 
        private $total_price; 
        private $status; 

        public function __construct($booking_id, 
         $customer_id,
         $machine_id,
         $slot_id,
         $service_id, 
         $manager_id, 
         $booking_reference, 
         $bookingDate,
         $machine_free_at, 
         $total_price, 
        $status ){
            $this->booking_id = $booking_id; 
            $this->customer_id = $customer_id; 
            $this->machine_id = $machine_id; 
            $this->slot_id = $slot_id; 
            $this->service_id = $service_id; 
            $this->manager_id = $manager_id; 
            $this->booking_reference = $booking_reference; 
            $this->bookingDate = $bookingDate; 
            $this->machine_free_at = $machine_free_at; 
            $this->total_price = $total_price; 
            $this->status = $status;
        }
    }