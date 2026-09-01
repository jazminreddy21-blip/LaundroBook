<?php

class Machine{
    private $machine_id; 
    private $manager_id; 
    private $machine_name; 
    private $machine_status; 

    public function __construct($machine_id, $manager_id, $machine_name, $machine_status){
        $this->machine_id = $machine_id;
        $this->manager_id = $manager_id;
        $this->machine_name = $machine_name;
        $this->machine_status = $machine_status;
    }

    public function getMachineId(){
        return $this->machine_id;
    }

    public function getMachineStatus(){
        return $this->machine_status;
    }
}