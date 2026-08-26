<?php

    interface CustomerRepositoryInterface{
        //add a customer
        public function createCustomer($customer_name, $customer_email, $customer_phone, $customer_address); 
        //find a customer
        public function findCustomer($customerId); 
        //update a customer
        public function updateCustomer($customerId); 
        //delete a customer
        public function deleteCustomer($customerId); 
    }