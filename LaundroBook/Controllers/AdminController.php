<?php
    require_once __DIR__ . '/../Interfaces/Repositoryinterfaces.php'; 
    require_once __DIR__ . '/../Repositories/SystemManagerRepo.php'; 
    require_once __DIR__ . '/../Repositories/BookingRepo.php';
    require_once __DIR__ . '/../Repositories/MachineRepo.php';
    require_once __DIR__ . '/../Repositories/DeliveryRepo.php';
    require_once __DIR__ . '/../Repositories/CustomerRepo.php';
    require_once __DIR__ . '/../Repositories/ServiceRepo.php';
    require_once __DIR__ . '/../Services/DashboardService.php';

    class AdminController{
        //validate the inputs on the server side by checking the database 
        //once validated successfully, then we can route the request to the system manager repo
        //which will then fetch the admin from the database and return a result if it fails
        private SystemManagerRepoInterface $systemManagerRepository; 
        private BookingRepoInterface $bookingRepository;
        private MachineRepoInterface $machineRepository;
        private DeliveryRepoInterface $deliveryRepository;
        private CustomerRepoInterface $customerRepository;
        private ServiceRepoInterface $serviceRepository;
        private DashboardService $dashboardService;

        // The extra repositories all default to the real concrete
        // class when not supplied, so the existing call sites
        // (Public/adminLogin.php, adminLogout.php) that only ever
        // pass a SystemManagerRepoInterface keep working unchanged,
        // while the dashboard/management pages can still inject
        // stubs for testing if needed.
        public function __construct(
            SystemManagerRepoInterface $systemManagerRepository,
            ?BookingRepoInterface $bookingRepository = null,
            ?MachineRepoInterface $machineRepository = null,
            ?DeliveryRepoInterface $deliveryRepository = null,
            ?CustomerRepoInterface $customerRepository = null,
            ?ServiceRepoInterface $serviceRepository = null
        ){
            $this->systemManagerRepository = $systemManagerRepository;
            $this->bookingRepository = $bookingRepository ?? new BookingRepo();
            $this->machineRepository = $machineRepository ?? new MachineRepo();
            $this->deliveryRepository = $deliveryRepository ?? new DeliveryRepo();
            $this->customerRepository = $customerRepository ?? new CustomerRepo();
            $this->serviceRepository = $serviceRepository ?? new ServiceRepo();

            $this->dashboardService = new DashboardService(
                $this->bookingRepository,
                $this->machineRepository,
                $this->deliveryRepository
            );
            }

        public function showLogin(){
            require_once __DIR__ . '/../Views/login.php'; 
        }


        public function login() {

            if($_SERVER['REQUEST_METHOD'] !== 'POST' ){
                $this->showLogin(); 
                return; 
            }

            $username = trim($_POST["username"] ?? ''); 
            $password = $_POST["password"] ?? ''; 

            if(empty($username) || empty($password)){
                //technically, this should be 
                $error = 'Username and password are required'; 
                require_once __DIR__ . '/../Views/login.php'; 
                return;  
            }
            $manager = $this->systemManagerRepository->findManager($username);

           
            
            if($manager == null || !password_verify($password, $manager->getPasswordHash())){
                $error = "Invalid email or password inserted"; 
                //need to display errors to show that credentials were wrong
                require_once __DIR__ . '/../Views/login.php'; 
                
                return; 
            }
            

            
            //starting a session based on the manager id
            $_SESSION['manager_id'] = $manager->getId();
            $_SESSION['username'] = $manager->getUsername(); 


            
            
            //preventing resubmission when dashboard is refreshed
            header('Location: ../Views/adminDash.php'); 
            exit; 
        }
        


        public function logout(){
            $_SESSION = [];

            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();

                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $params['path'],
                    $params['domain'],
                    $params['secure'],
                    $params['httponly']
                );
            }

            session_destroy();

            header('Location: ../Views/login.php');
            exit;
        }
        public function requireAuthentication(){
            if(!isset($_SESSION['manager_id'])){
                header('Location: login.php');
                exit; 
            }
        }

        // ------------------------------------------------------------
        // Data-gathering methods for the admin pages. Each one is
        // called directly by its matching View (adminDash.php,
        // bookingManagement.php, etc.) right after
        // requireAuthentication(), and just returns plain arrays for
        // the view to render - no HTML is built in here, keeping the
        // controller/view split the rest of the app already uses.
        // ------------------------------------------------------------

        public function getDashboardData(): array
        {
            return [
                'stats' => $this->dashboardService->getStats(),
                'notifications' => $this->dashboardService->getNotifications(),
            ];
        }

        public function getBookings(array $filters = []): array
        {
            return $this->bookingRepository->getAllBookings($filters);
        }

        public function getPickups(array $filters = []): array
        {
            $filters['type'] = 'collection';
            return $this->deliveryRepository->getAll($filters);
        }

        public function getDeliveries(array $filters = []): array
        {
            $filters['type'] = 'delivery';
            return $this->deliveryRepository->getAll($filters);
        }

        public function getMachines(): array
        {
            return $this->machineRepository->getAllMachines();
        }

        public function getCustomers(string $search = ''): array
        {
            return $this->customerRepository->getAllCustomers($search);
        }

        // Simple report figures for the last 7/30 days. Kept here
        // rather than a dedicated ReportService since it's just a
        // handful of read-only aggregate calls, nothing that
        // coordinates a write or a transaction the way BookingService
        // does.
        public function getReportData(): array
        {
            $today = date('Y-m-d');
            $last7 = date('Y-m-d', strtotime('-6 days'));
            $last30 = date('Y-m-d', strtotime('-29 days'));

            return [
                'revenue_7_days' => $this->bookingRepository->revenueBetween($last7, $today),
                'revenue_30_days' => $this->bookingRepository->revenueBetween($last30, $today),
                'completed_30_days' => $this->bookingRepository->countByStatusBetween('completed', $last30, $today),
                'cancelled_30_days' => $this->bookingRepository->countByStatusBetween('cancelled', $last30, $today),
                'total_customers' => $this->customerRepository->countAll(),
                'services' => $this->serviceRepository->getAllServices(),
            ];
        }

        // ------------------------------------------------------------
        // POST action handlers, called from Public/adminAction.php.
        // Every one re-checks authentication itself, since they can be
        // hit directly rather than through a page that already called
        // requireAuthentication().
        // ------------------------------------------------------------

        public function updateBookingStatus(): void
        {
            $this->requireAuthentication();

            $reference = trim($_POST['booking_reference'] ?? '');
            $status = trim($_POST['status'] ?? '');
            $booking = $reference !== '' ? $this->bookingRepository->findBookingByReference($reference) : null;

            if ($booking === null) {
                header('Location: ../Views/bookingManagement.php?error=not_found');
                exit;
            }

            try {
                $this->bookingRepository->updateStatus((int)$booking['booking_id'], $status);

                // Declining/cancelling a booking frees up the machine
                // again, the same way BookingService flips it to
                // 'in_use' when a booking is first created.
                if (strtolower($status) === 'cancelled') {
                    $this->machineRepository->updateStatus((int)$booking['machine_id'], 'available');
                }
            } catch (InvalidArgumentException $e) {
                header('Location: ../Views/bookingManagement.php?error=invalid_status');
                exit;
            }

            header('Location: ../Views/bookingManagement.php?updated=1');
            exit;
        }

        public function updateMachineStatusAction(): void
        {
            $this->requireAuthentication();

            $machineId = (int)($_POST['machine_id'] ?? 0);
            $status = trim($_POST['status'] ?? '');

            try {
                $this->machineRepository->updateStatus($machineId, $status);
            } catch (InvalidArgumentException $e) {
                header('Location: ../Views/machineManagement.php?error=invalid_status');
                exit;
            }

            header('Location: ../Views/machineManagement.php?updated=1');
            exit;
        }

        public function updateDeliveryStatusAction(): void
        {
            $this->requireAuthentication();

            $deliveryId = (int)($_POST['delivery_id'] ?? 0);
            $status = trim($_POST['status'] ?? '');
            $type = trim($_POST['delivery_type'] ?? 'collection');
            $redirectPage = $type === 'delivery' ? 'deliveryManagement.php' : 'pickupManagement.php';

            try {
                $this->deliveryRepository->updateStatus($deliveryId, $status);
            } catch (InvalidArgumentException $e) {
                header("Location: ../Views/{$redirectPage}?error=invalid_status");
                exit;
            }

            header("Location: ../Views/{$redirectPage}?updated=1");
            exit;
        }
    }