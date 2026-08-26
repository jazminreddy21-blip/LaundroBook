<?php
    require_once __DIR__ . "/../interfaces/CustomerRepositoryInterface.php";
    require_once __DIR__ . "/../models/Customer.php"; 

    class customerRepository implements CustomerRepositoryInterface{
        private static $fakeIdCounter = 1; //dummy id-gen to simulate what the db will generate for us

        public function createCustomer($customer_name, $customer_email, $customer_phone, $customer_address){
            //this is where the construction of the customer object would occur
            /*
                INSERT INTO Customer (name, email, phone, address) VALUES().......
            */
                //we retrieve the auto-generated id from the database
            //echo $customer_name. "". $customer_email. "". $customer_phone. "". $customer_address; 
            
            
            $newId = self::$fakeIdCounter;
            self::$fakeIdCounter++;
            echo "Assigned id: " . $newId; 
            //then once we've inserted we construct and return the object 
            return new Customer($newId, $customer_name, $customer_email, $customer_phone, $customer_address); 
        } 
        //find a customer
        public function findCustomer($customerId){

        } 
        //update a customer
        public function updateCustomer($customerId){

        } 
        //delete a customer
        public function deleteCustomer($customerId){

        } 



    }