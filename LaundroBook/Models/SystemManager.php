<?php

class SystemManager{
    private $manager_id; 
    private $username; 
    private $password_hash; 


        
    public function __construct($manager_id, $username, $password_hash){
        $this->manager_id = $manager_id;
        $this->username = $username; 
        $this->password_hash = $password_hash; 
    }

    public function getId(){
        return $this->manager_id; 
    }

}