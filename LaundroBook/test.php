<?php

    //echo __DIR__; 
require_once __DIR__ . '/repositories/SlotRepository.php';
require_once __DIR__ . '/controllers/BookingController.php';
require_once __DIR__ . '/repositories/MachineRepository.php'; 
require_once __DIR__ . '/repositories/CustomerRepository.php';
require_once __DIR__ . '/repositories/ServiceRepository.php';
require_once __DIR__ . '/repositories/SystemManagerRepository.php';




//echo 'I work by the way!'; 

function assertEqual($expected, $actual, $testName)
{
    if ($expected === $actual) {
        echo "PASS: $testName\n";
    } else {
        $expectedStr = var_export($expected, true);
        $actualStr = var_export($actual, true);
        echo "FAIL: $testName (expected $expectedStr, got $actualStr)\n";
    }
}


$slotRepository = new SlotRepository(); // real class, hardcoded body for now
$machineRepository = new MachineRepository(); 
$customerRepository = new CustomerRepository(); 
$serviceRepository = new ServiceRepository(); 
$systemManagerRepository = new SystemManagerRepository(); 
$bookingController = new BookingController($slotRepository, $machineRepository, $customerRepository, $serviceRepository, $systemManagerRepository);

$slot_list = $bookingController->availableSlots(); 
$customer = $bookingController->createCustomerObject("Mabutho", "mabuthosiyanda83@gmail.com", "0789108501", "116 Currie Rd"); 
$systemManager = $bookingController->retrieveManager(1); 



//should simply just print the retrieved service here
echo "<br>"; 
echo "<br>"; 

//$bookingController->retrieveService("quick", "clothes", 30.00, 25.00);

/*
foreach($slot_list as $slot){
    echo $slot."<br>"; 
}
*/
echo "<br>"; 
echo "<br>"; 

//system manager unit test
assertEqual(true, $systemManager, "Manager exists"); 


//testing if the customer creation returns the customer object and their id assigned 

//$customer->printCustomerDetails(); 

//slot list then returns all the available slots as an array
//using unit tests to check if the slotRepo Works properly and it does :)

/*
assertEqual(true, $bookingController->findSlot(1), "Slot 1 should be available");
assertEqual(false, $bookingController->findSlot(2), "Slot 2 should be unavailable");
assertEqual(false, $bookingController->findSlot(99), "Nonexistent slot should be unavailable");
*/



//unit tests for machine repo and it's interface:
/*
assertEqual(true, $bookingController->findMachine(1), "Machine 1 should be available");
assertEqual(false, $bookingController->findMachine(2), "Machine 2 should be unavailable");
assertEqual(false, $bookingController->findMachine(99), "Nonexistent machine should be unavailable");
*/


