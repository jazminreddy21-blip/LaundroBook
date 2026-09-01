<?php

class Customer{
    private $customer_id;
    private $customer_name;
    private $customer_email;
    private $customer_phone;
    private $customer_address;

    public function __construct($id, $name, $email, $phone, $address)
    {
        $this->customer_id = $id;
        $this->customer_name = $name;
        $this->customer_email = $email;
        $this->customer_phone = $phone;
        $this->customer_address = $address;
    }

    public function getCustomerId(){
        return $this->customer_id;
    }

    public function printCustomerDetails()
    {
        echo "Customer id: " . $this->customer_id . "<br>";
        echo "Customer name: " . $this->customer_name . "<br>";
        echo "Customer email: " . $this->customer_email . "<br>";
        echo "Customer phone: " . $this->customer_phone . "<br>";
        echo "Customer address: " . $this->customer_address;
    }
}