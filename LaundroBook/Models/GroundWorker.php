<?php

class GroundWorker{
    private $groundworker_id; 
    private $groundworker_name; 
    private $groundworker_phone; 
    private $groundworker_role; 

    public function __construct($groundworker_id, $groundworker_name, $groundworker_phone, $groundworker_role){
        $this->groundworker_id = $groundworker_id; 
        $this->groundworker_name = $groundworker_name; 
        $this->groundworker_phone = $groundworker_phone; 
        $this->groundworker_role = $groundworker_role; 
    }

    public function getGroundworkerId(){
        return $this->groundworker_id;
    }
    
}