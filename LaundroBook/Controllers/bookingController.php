<?php
    class BookingController{
        private $slotRepository;
        private $machineRepository; 
        private $customerRepository; 
        private $bookingRepository;
        private $serviceRepository; 
        private $systemManagerRepository;


        public function __construct(SlotRepositoryInterface $slotRepository,
         MachineRepositoryInterface $machineRepository,//,
          CustomerRepositoryInterface $customerRepository, 
          BookingRepositoryInterface $bookingRepository, 
          ServiceRepositoryInterface $serviceRepository, 
          SystemManagerRepository $systemManagerRepository
          )
          {
            $this->slotRepository = $slotRepository; 
            $this->machineRepository = $machineRepository; 
            $this->customerRepository = $customerRepository; 
            $this->bookingRepository = $bookingRepository; 
            $this->serviceRepository = $serviceRepository; 
            $this->systemManagerRepository = $systemManagerRepository; 
        }
            
        public function validate_input(){
        }
        //once input has been validated, then we can proceed with the routing process
        //we'll then use the slot time var which has the slot id?
        public function findSlot($SlotId){
            return $this->slotRepository->findAvailableSlot($SlotId); 
        }

        public function findMachine($MachineId){
            return $this->machineRepository->findAvailableMachine($MachineId); 
        }

        public function availableSlots(){
            return $this->slotRepository->returnAvailableSlots(); 
        }

        public function availableMachines(){
            return $this->machineRepository->returnAvailableMachines(); 
        }
        //after all the checks: validation, slot check and machine availability checks, 
        //we can now create the customer here, then create the booking 
        public function createCustomerObject($customer_name, $customer_email, $customer_phone, $customer_address){
            
            return $this->customerRepository->createCustomer($customer_name, $customer_email, $customer_phone, $customer_address); 
        }

        public function retrieveService($wash_type, $load_type, $price, $duration_minutes){
            //verification can work through simply retrieving 
            // all the services data from the db (service objects)
            //then checking if the user selected services are there

            //this function has to be responsible for verifying 
            //that the service selected by the customer was
            //valid and the prices were not tinkered with
            $services_retrieved = $this->serviceRepository->getServices();

            
            //verification 



            //retrieve the original service in the database and 
            //compare it with the one selected by the customer
        }

        //manager_id param is used for testing purposes
        public function retrieveManager($manager_id){

            return $this->systemManagerRepository->getSystemManager();
        }

        //passing input params from the other created objects
        public function createBooking($customer_id, $machine_id, $slot_id, $service_id, $manager_id, $total_price){
            return $this->bookingRepository->createBooking($customer_id, $machine_id, $slot_id, $service_id, $manager_id, $total_price);    
        }

    };
    /*
            behaviors within this controller:
            -> validate input
            -> check for slot availability (only future slots where start_time >= current time)
            -> check for machine availability (machine_status = available)
            -> check consecutive slot availability if duration_slots = 2 (Heavy Wash)
            -> create customer if they don't exist
            -> fetch service (price + duration_minutes + duration_slots)
            -> derive manager_id
            -> calculate total_price
            -> generate booking_reference
            -> set initial status to Pending
            -> begin transaction
                -> insert booking record
                -> if duration_slots = 2, insert second booking record for consecutive slot
                -> update machine status to in_use
            -> commit transaction
            -> send confirmation email
            -> return confirmation to booking page



        */