<?php

class Delivery{

    private $delivery_id; 
    private $booking_id; 
    private $groundworker_id; 
    private $delivery_type; 
    private $delivery_status; 
    private $scheduled_time; 


    public function __construct($delivery_id, $booking_id, $groundworker_id, $delivery_type
        , $delivery_status, $scheduled_time){
        $this->delivery_id = $delivery_id; 
        $this->booking_id = $booking_id; 
        $this->groundworker_id = $groundworker_id; 
        $this->delivery_type = $delivery_type;
        $this->delivery_status = $delivery_status; 
        $this->scheduled_time = $scheduled_time; 
    }

    public function getDeliveryId(){
        return $this->delivery_id;
    }

    public function getDeliveryStatus(){
        return $this->delivery_status;
    }

}
