<?php

class Booking
{
    private $booking_id;
    private $customer_id;
    private $machine_id;
    private $slot_id;
    private $service_id;
    private $manager_id;
    private $booking_reference;
    private $booking_date;
    private $total_price;
    private $status;
 
    public function __construct(
        $booking_id,
        $customer_id,
        $machine_id,
        $slot_id,
        $service_id,
        $manager_id,
        $booking_reference,
        $booking_date,
        $total_price,
        $status
    ) {
        $this->booking_id = $booking_id;
        $this->customer_id = $customer_id;
        $this->machine_id = $machine_id;
        $this->slot_id = $slot_id;
        $this->service_id = $service_id;
        $this->manager_id = $manager_id;
        $this->booking_reference = $booking_reference;
        $this->booking_date = $booking_date;
        $this->total_price = $total_price;
        $this->status = $status;
    }
 
    public function getBookingId()
    {
        return $this->booking_id;
    }
 
    public function getBookingReference()
    {
        return $this->booking_reference;
    }
 
    public function printBookingDetails()
    {
        echo "Booking reference: " . $this->booking_reference . "<br>";
        echo "Booking date: " . $this->booking_date . "<br>";
        echo "Total price: R" . $this->total_price . "<br>";
        echo "Status: " . $this->status;
    }
}
