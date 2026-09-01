<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../Services/BookingService.php';

class bookingController{
    /*
        behaviors within this controller:
        -> validate input -> done
        -> check for slot availability (only future slots where start_time >= current time) -> not done
           (AvailabilityService checks is_active and whether a slot is already
           booked, but nothing filters out slots whose start_time has already
           passed today - the 08:00 slot could still be booked at 11am)
        -> check for machine availability (machine_status = available) -> done
        -> check consecutive slot availability if duration_slots = 2 (Heavy Wash) -> done, with a gap
           (handled correctly when building the list shown to the customer, but
           the final race-condition re-check in createBooking() only re-checks
           slot_id, not second_slot_id, so a Heavy Wash's second slot never
           gets re-verified right before insert)
        -> findOrcustomer if they don't exist -> done
           ($this->customerRepo->findOrCreate($data), inside createBooking() below)
        -> fetch service (price + duration_minutes + duration_slots) -> done
        -> derive manager_id -> done
        -> calculate total_price -> done
        -> generate booking_reference -> done
           (built inside BookingRepo::insert()/updateReference() right after the
           row is created, and re-built a second time down in createBooking()
           return value using the same formula, duplicated logic, not a bug,
           but worth tidying up later)
        -> set initial status to Pending -> done
        -> begin transaction -> done
            -> insert booking record -> done
            -> if duration_slots = 2, insert second booking record for consecutive slot -> done
            -> update machine status to in_use -> done
        -> commit transaction -> done
        -> send confirmation email -> not done
           (no EmailService exists yet - nothing in this file sends anything)
        -> return confirmation to booking page -> done
           (redirect to BookingConfirm.php with the full receipt in session -
           reference, email, name, date, service, duration, machine name, and
           slot labels, all looked up server-side rather than trusted from
           the form, so the receipt only ever shows real database values)

        everything from "findOrcustomer" now lives in
        BookingService::createBooking(), not in this controller. It used
        to be folded directly into this controller, but it was
        coordinating five repositories plus a real database transaction,
        business logic, not something a controller should hold.
        This controllers job now is just to validate input, call
        BookingService, and turn the result into a redirect.
    */
    private $errors = []; // array for errors
    private $data = []; // array for validated form inputs

    private BookingService $bookingService;

    public function __construct(BookingService $bookingService){
        $this->bookingService = $bookingService;
    }

    public function validate_input(){

        if(!$_POST){
            $this->errors[] = "No data submitted";
            return false;
        }

        //inputs:
        $customer_name = $this->sanitize($_POST['customer_name'] ?? '');
        $customer_email = $this->sanitize($_POST['customer_email'] ?? '');
        $customer_phone = $this->sanitize($_POST['customer_phone'] ?? ''); 
        

        
        //selects: 
        $booking_date = $this->sanitize($_POST['booking_date'] ?? '');
        $wash_type = $this->sanitize($_POST['wash_type'] ?? '');
        $load_type = $this->sanitize($_POST['load_type'] ?? ''); 
        $collection_method = $this->sanitize($_POST["collection_method"] ?? ''); 
        $machine_number = $this->sanitize($_POST["machine_id"] ?? '');
        $slot_time = $this->sanitize($_POST['slot_id'] ?? '');
        // second_slot_id only arrives when the customers chosen combo was
        // a Heavy Wash (duration_slots = 2) - AvailabilityController would
        // have returned it alongside slot_id for that combo.
        $second_slot_time = $this->sanitize($_POST['second_slot_id'] ?? '');

        // defaults to null instead of an empty string
        $delivery_address = null;

        if($collection_method == "delivery"){
            $delivery_address = $this->sanitize($_POST['delivery_address'] ?? '');

            if(empty($delivery_address)){
                $this->errors[] = "Address is required";
            }

        }


        //customer information validation:

        if(empty($customer_name)){
            $this->errors[] = "Full name is required";
        }
        else if(strlen($customer_name) < 2){
            $this->errors[] = "Full name must be more than 2 characters";
        }
        else if(!preg_match("/^[a-zA-Z\s\-'.]+$/", $customer_name)){
            $this->errors[] = "Full name must only conatin letters";
        }


        if(empty($customer_email)){
            $this->errors[] = "Email is required";
        }
        else if(!filter_var($customer_email, FILTER_VALIDATE_EMAIL)){
            $this->errors[] = "Please enter a valid email address";
        }


        if(empty($customer_phone)){
            $this->errors[] = "Phone number is required";
        }
        else if(!preg_match("/^[0-9]+$/", $customer_phone)){
            $this->errors[] = "Phone numbers must only contain numbers";
        }
        else if(strlen($customer_phone) != 10){
            $this->errors[] = "Phone number must be 10 digits";
        }


        //booking information validation:

        if(empty($booking_date)){
            $this->errors[] = "Booking data is required";
        }
        else if(!$this->is_valid_date($booking_date)){
            $this->errors[] = "Booking date format is invalid";
        }
        else{
            $today = new DateTime('today');
            $selected = DateTime::createFromFormat('Y-m-d', $booking_date);
            if($selected < $today){
                $this->errors[] = "Booking date cannot be in the past";
            }
        }


        $valid_wash_types = ['quick', 'normal', 'heavy'];
        if(empty($wash_type) || !in_array($wash_type, $valid_wash_types, true)){
            $this->errors[] = "Please select a valid wash type";
        }


        $valid_load_types = ['clothes', 'beddings', 'towels'];
        if(empty($load_type) || !in_array($load_type, $valid_load_types, true)){
            $this->errors[] = "Please select a valid load type";
        }

        
        $valid_collection = ['pickup', 'delivery'];
        if(empty($collection_method) || !in_array($collection_method, $valid_collection, true)){
            $this->errors[] = "Please select a valid collection method";
        }


        if(empty($machine_number) || !ctype_digit((string)$machine_number)){
            $this->errors[] = "A valid machine selection is required";
        }


        if(empty($slot_time) || !ctype_digit((string)$slot_time)){
            $this->errors[] = "A valid time slot selection is required";
        }


        if(!empty($this->errors)){
            return false;
        }


        //Stashing the clean data for future steps before the function ends, prevents going through whole $_POST submission -
        // where users can enter unsanitized data, leading to duplicated sanitization process.
        // NOTE: keys renamed from machine_number/slot_time to machine_id/slot_id
        // here (int-cast) since that's what BookingService expects further down, the local variable
        $this->data = [
            'customer_name' => $customer_name,
            'customer_email' => $customer_email,
            'customer_phone' => $customer_phone,
            'booking_date' => $booking_date,
            'wash_type' => $wash_type,
            'load_type' => $load_type,
            'collection_method' => $collection_method,
            'delivery_address' => $delivery_address,
            'machine_id' => (int)$machine_number,
            'slot_id' => (int)$slot_time,
            'second_slot_id' => $second_slot_time !== '' ? (int)$second_slot_time : null,
        ];


        return true;

    }

    //sanitization function
    private function sanitize($value){
        return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
    }

    //valid date check function
    private function is_valid_date($date, $format = 'Y-m-d'){
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    public function getErrors(){
        return $this->errors;
    }

    public function getData(){
        return $this->data;
    }

    /*
        The actual write endpoint, what "Confirm Booking" calls.
        Not to be confused with a read-only availability check, which
        should already have happened before this point and never
        writes anything.

        confirmBookingBtn is type="submit" doing a native form
        submission (no fetch()), so JSON was never actually usable
        here, a plain form submit cannot do anything with a JSON
        response except display the raw text on screen. Every outcome
        now redirects instead: success goes to the receipt page,
        failures go back to the booking form with the errors stashed
        in session for booking.php to display on reload.
    */
    public function confirmBooking(){
        if(!$this->validate_input()){
            $_SESSION['booking_errors'] = $this->getErrors();
            header('Location: ../Views/booking.php');
            exit;
        }

        $result = $this->bookingService->createBooking($this->data);

        if(!$result['success']){
            $_SESSION['booking_errors'] = $result['errors'];
            header('Location: ../Views/booking.php');
            exit;
        }

        // Success, everything the receipt page needs to show gets
        // stashed in session rather than the URL, so nothing ends up
        // visible in the address bar or browser history, then redirect
        // to the receipt page. All of these come from $result (server
        // lookups) or $this->data (already-validated form input),
        // nothing here is trusted straight from raw $_POST.
        $_SESSION['booking_reference'] = $result['booking_reference'];
        $_SESSION['customer_email'] = $this->data['customer_email'];
        $_SESSION['customer_name'] = $this->data['customer_name'];
        $_SESSION['booking_date'] = $this->data['booking_date'];
        $_SESSION['wash_type'] = $result['wash_type'];
        $_SESSION['load_type'] = $result['load_type'];
        $_SESSION['duration_minutes'] = $result['duration_minutes'];
        $_SESSION['machine_name'] = $result['machine_name'];
        $_SESSION['slot_label'] = $result['slot_label'];
        $_SESSION['second_slot_label'] = $result['second_slot_label'];
        $_SESSION['collection_method'] = $this->data['collection_method'];
        $_SESSION['delivery_address'] = $this->data['delivery_address'];

        header('Location: ../Views/BookingConfirm.php');
        exit;
    }

};

/*
    Wiring, only runs when this file is hit directly as the forms
    POST target, not when bookingController is required elsewhere
    (e.g. by a test file that builds one with stub repos instead).
*/
if(($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && basename($_SERVER['SCRIPT_NAME']) === 'bookingController.php'){
    require_once __DIR__ . '/../Repositories/MachineRepo.php';
    require_once __DIR__ . '/../Repositories/SlotRepo.php';
    require_once __DIR__ . '/../Repositories/CustomerRepo.php';
    require_once __DIR__ . '/../Repositories/BookingRepo.php';
    require_once __DIR__ . '/../Repositories/ServiceRepo.php';
    require_once __DIR__ . '/../Services/AvailabilityService.php';

    $machineRepo = new MachineRepo();
    $slotRepo = new SlotRepo();
    $customerRepo = new CustomerRepo();
    $bookingRepo = new BookingRepo();
    $serviceRepo = new ServiceRepo();

    $availability = new AvailabilityService($machineRepo, $slotRepo, $bookingRepo);
    $bookingService = new BookingService($machineRepo, $slotRepo, $customerRepo, $bookingRepo, $serviceRepo, $availability);

    $controller = new bookingController($bookingService);
    $controller->confirmBooking();
}