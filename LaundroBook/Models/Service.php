<?php

class Service{
    private $service_id; 
    private $manager_id; 
    private $wash_type; 
    private $load_type; 
    private $price; 
    private $duration_minutes; 
    private $duration_slots;

    public function __construct($service_id, $manager_id, $wash_type
        , $load_type, $price, $duration_minutes, $duration_slots){
        $this->service_id = $service_id; 
        $this->manager_id = $manager_id; 
        $this->wash_type = $wash_type; 
        $this->load_type = $load_type; 
        $this->price = $price; 
        $this->duration_minutes = $duration_minutes; 
        $this->duration_slots = $duration_slots; 
    }

    public function __toString(){
        return "Service id: ". $this->service_id . "<br>".
        "Manager id: ". $this->manager_id . "<br>". 
        "Wash type: " . $this->wash_type . "<br>". 
        "Load type: " . $this->load_type . "<br>". 
        "Price: " . $this->price . "<br>" . 
        "Duration in mins: " . $this->duration_minutes. "<br>". 
        "Duration slots: " . $this->duration_slots; 
    }

}