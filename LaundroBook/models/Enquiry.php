<?php

    class Enquiry{
        private $enquiry_id;
        private $manager_id; 
        private $name; 
        private $email; 
        private $message; 
        private $date_submitted; 
        private $status; 

        public function __construct($enquiry_id, $manager_id, $name, $email, $message, $date_submitted, 
        $status){
            $this->enquiry_id = $enquiry_id;
            $this->manager_id = $manager_id; 
            $this->name = $name; 
            $this->email = $email;
            $this->message = $message; 
            $this->date_submitted = $date_submitted; 
            $this->status = $status; 
        }
    }