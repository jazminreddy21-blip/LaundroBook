<?php
    require_once __DIR__ . '/../Interfaces/Repositoryinterfaces.php'; 
    require_once __DIR__ . '/../Repositories/SystemManagerRepo.php'; 
    require_once __DIR__ . '/../Repositories/BookingRepo.php';
    require_once __DIR__ . '/../Repositories/MachineRepo.php';
    require_once __DIR__ . '/../Repositories/DeliveryRepo.php';
    require_once __DIR__ . '/../Repositories/CustomerRepo.php';
    require_once __DIR__ . '/../Repositories/ServiceRepo.php';
    require_once __DIR__ . '/../Repositories/GroundworkerRepo.php';
    require_once __DIR__ . '/../Repositories/EnquiryRepo.php';
    require_once __DIR__ . '/../Services/DashboardService.php';
    require_once __DIR__ . '/../Interfaces/EmailServiceInterface.php';
    require_once __DIR__ . '/../Services/EmailService.php';

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
        private GroundworkerRepo $groundworkerRepository;
        private EnquiryRepoInterface $enquiryRepository;
        private EmailServiceInterface $emailService;
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
            ?ServiceRepoInterface $serviceRepository = null,
            ?GroundworkerRepo $groundworkerRepository = null,
            ?EnquiryRepoInterface $enquiryRepository = null,
            ?EmailServiceInterface $emailService = null
        ){
            $this->systemManagerRepository = $systemManagerRepository;
            $this->bookingRepository = $bookingRepository ?? new BookingRepo();
            $this->machineRepository = $machineRepository ?? new MachineRepo();
            $this->deliveryRepository = $deliveryRepository ?? new DeliveryRepo();
            $this->customerRepository = $customerRepository ?? new CustomerRepo();
            $this->serviceRepository = $serviceRepository ?? new ServiceRepo();
            $this->groundworkerRepository = $groundworkerRepository ?? new GroundworkerRepo();
            $this->enquiryRepository = $enquiryRepository ?? new EnquiryRepo();
            // Same config-loading pattern already used in
            // bookingController.php's wiring block.
            $this->emailService = $emailService ?? new EmailService(require __DIR__ . '/../Config/EmailConfig.php');

            $this->dashboardService = new DashboardService(
                $this->bookingRepository,
                $this->machineRepository,
                $this->deliveryRepository,
                $this->enquiryRepository
            );
            }

        public function showLogin(){
            require_once __DIR__ . '/../Views/Admin/login.php'; 
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
                require_once __DIR__ . '/../Views/Admin/login.php'; 
                return;  
            }
            $manager = $this->systemManagerRepository->findManager($username);

           
            
            if($manager == null || !password_verify($password, $manager->getPasswordHash())){
                $error = "Invalid email or password inserted"; 
                //need to display errors to show that credentials were wrong
                require_once __DIR__ . '/../Views/Admin/login.php'; 
                
                return; 
            }
            

            
            //starting a session based on the manager id
            $_SESSION['manager_id'] = $manager->getId();
            $_SESSION['username'] = $manager->getUsername(); 

            // Computed fresh here, not stored on the manager record -
            // whoever currently has the lowest manager_id is the
            // super admin, so this stays correct automatically even
            // if the original super admin account is ever deleted.
            $_SESSION['is_super_admin'] = $this->systemManagerRepository->isSuperAdmin($manager->getId());


            
            
            //preventing resubmission when dashboard is refreshed
            header('Location: ../Views/Admin/adminDash.php'); 
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

            header('Location: ../Views/Admin/login.php');
            exit;
        }
        public function requireAuthentication(){
            if(!isset($_SESSION['manager_id'])){
                header('Location: login.php');
                exit; 
            }
        }

        // Used by adminRegister.php and registerAdmin() below - blocks
        // any admin whose session wasn't flagged as super admin at
        // login time. Called AFTER requireAuthentication(), same as
        // every other page's auth check, this just adds the extra
        // role restriction on top.
        public function requireSuperAdmin(){
            if(!($_SESSION['is_super_admin'] ?? false)){
                header('Location: adminDash.php?error=not_super_admin');
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

        // Used by deliveryManagement.php / pickupManagement.php to
        // populate the reassignment dropdown.
        public function getGroundworkers(): array
        {
            return $this->groundworkerRepository->getAllGroundworkers();
        }

        // Used by enquiryManagement.php.
        public function getEnquiries(string $search = ''): array
        {
            return $this->enquiryRepository->getAll($search);
        }

        // Used by adminRegister.php's admin list - super-admin gated at
        // the view level, same as the rest of that page.
        public function getAdmins(): array
        {
            return $this->systemManagerRepository->getAllManagers();
        }

        // Used by adminRegister.php to mark which row in the admin
        // list is the super admin, so it can be shown as non-
        // removable. Thin wrapper around the repo's own dynamic
        // MIN(manager_id) check.
        public function isSuperAdmin(int $managerId): bool
        {
            return $this->systemManagerRepository->isSuperAdmin($managerId);
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
                header('Location: ../Views/Admin/bookingManagement.php?error=not_found');
                exit;
            }

            try {
                $this->bookingRepository->updateStatusForGroup((int)$booking['booking_id'], $status);

                // Declining/cancelling a booking frees up the machine
                // again, the same way BookingService flips it to
                // 'in_use' when a booking is first created.
                if (strtolower($status) === 'cancelled') {
                    $this->machineRepository->updateStatus((int)$booking['machine_id'], 'available');

                    // ADDED: let the customer know their booking was
                    // cancelled, rather than them only finding out by
                    // checking the tracking page themselves.
                    $customer = $this->customerRepository->findById((int)$booking['customer_id']);
                    $service = $this->serviceRepository->getServiceById((int)$booking['service_id']);

                    if ($customer !== null && $service !== null) {
                        $this->emailService->sendOrderCancelledEmail(
                            $customer->getCustomerEmail(),
                            $booking['booking_reference'],
                            $service
                        );
                    }
                }

                // ADDED: notify the customer their laundry is ready -
                // fire-and-forget, same contract as the original
                // booking confirmation email in BookingService. A
                // walk-in "Self Drop off and Pickup" customer has no
                // other way of knowing their wash is done, so this
                // fires for every completed booking, not just
                // delivery/pickup ones.
                if (strtolower($status) === 'completed') {
                    $customer = $this->customerRepository->findById((int)$booking['customer_id']);
                    $service = $this->serviceRepository->getServiceById((int)$booking['service_id']);

                    if ($customer !== null && $service !== null) {
                        $this->emailService->sendOrderCompleteEmail(
                            $customer->getCustomerEmail(),
                            $booking['booking_reference'],
                            $service
                        );
                    }
                }
            } catch (InvalidArgumentException $e) {
                header('Location: ../Views/Admin/bookingManagement.php?error=invalid_status');
                exit;
            }

            header('Location: ../Views/Admin/bookingManagement.php?updated=1');
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
                header('Location: ../Views/Admin/machineManagement.php?error=invalid_status');
                exit;
            }

            header('Location: ../Views/Admin/machineManagement.php?updated=1');
            exit;
        }

        public function updateDeliveryStatusAction(): void
        {
            $this->requireAuthentication();

            $deliveryId = (int)($_POST['delivery_id'] ?? 0);
            $status = trim($_POST['status'] ?? '');
            $type = trim($_POST['delivery_type'] ?? 'collection');
            $redirectPage = $type === 'delivery' ? 'deliveryManagement.php' : 'pickupManagement.php';

            $deliveryRow = $this->deliveryRepository->findById($deliveryId);

            if ($deliveryRow === null) {
                header("Location: ../Views/Admin/{$redirectPage}?error=invalid_status");
                exit;
            }

            $bookingId = (int)$deliveryRow['booking_id'];

            // The full chain: neither leg can move past pending while
            // the booking itself is still pending (an admin has to
            // explicitly move the booking to in_progress first, via
            // bookingManagement.php, before any pickup/delivery work
            // is considered to have genuinely started). On top of
            // that, the delivery leg specifically also can't move past
            // pending until the collection leg is completed - the
            // laundry has to actually be collected before it can be
            // washed and returned.
            if ($status !== 'pending') {
                $booking = $this->bookingRepository->findBooking($bookingId);

                if ($booking === null || strtolower($booking['status']) === 'pending') {
                    header("Location: ../Views/Admin/{$redirectPage}?error=booking_not_started");
                    exit;
                }

                if ($type === 'delivery') {
                    $collectionLeg = $this->deliveryRepository->findByBookingId($bookingId, 'collection');

                    if ($collectionLeg !== null && strtolower($collectionLeg['delivery_status']) !== 'completed') {
                        header("Location: ../Views/Admin/{$redirectPage}?error=collection_not_complete");
                        exit;
                    }
                }
            }

            try {
                $this->deliveryRepository->updateStatus($deliveryId, $status);
            } catch (InvalidArgumentException $e) {
                header("Location: ../Views/Admin/{$redirectPage}?error=invalid_status");
                exit;
            }

            // The last link in the chain - once the delivery leg is
            // genuinely completed, the whole booking is done, so the
            // booking itself is automatically marked completed too,
            // rather than requiring the admin to separately remember
            // to do this on bookingManagement.php as well.
            if ($type === 'delivery' && $status === 'completed') {
                $this->bookingRepository->updateStatus($bookingId, 'completed');
            }

            header("Location: ../Views/Admin/{$redirectPage}?updated=1");
            exit;
        }

        // Admin's manual reassignment of which groundworker is handling
        // a delivery/pickup - separate action from the status update
        // above, since they're conceptually different changes even
        // though they both act on the same delivery row.
        public function reassignGroundworker(): void
        {
            $this->requireAuthentication();

            $deliveryId = (int)($_POST['delivery_id'] ?? 0);
            $groundworkerId = (int)($_POST['groundworker_id'] ?? 0);
            $type = trim($_POST['delivery_type'] ?? 'collection');
            $redirectPage = $type === 'delivery' ? 'deliveryManagement.php' : 'pickupManagement.php';

            if ($deliveryId <= 0 || $groundworkerId <= 0) {
                header("Location: ../Views/Admin/{$redirectPage}?error=invalid_status");
                exit;
            }

            $this->deliveryRepository->reassignGroundworker($deliveryId, $groundworkerId);

            header("Location: ../Views/Admin/{$redirectPage}?updated=1");
            exit;
        }

        public function updateEnquiryStatusAction(): void
        {
            $this->requireAuthentication();

            $enquiryId = (int)($_POST['enquiry_id'] ?? 0);
            $status = trim($_POST['status'] ?? '');

            try {
                $this->enquiryRepository->updateStatus($enquiryId, $status);
            } catch (InvalidArgumentException $e) {
                header('Location: ../Views/Admin/enquiryManagement.php?error=invalid_status');
                exit;
            }

            header('Location: ../Views/Admin/enquiryManagement.php?updated=1');
            exit;
        }

        // Only reachable by the super admin - requireSuperAdmin() below
        // sends anyone else back to adminDash.php before this ever
        // runs. A new admin created here has no special status of its
        // own - being super admin is purely about having the lowest
        // manager_id (see SystemManagerRepo::isSuperAdmin()), not
        // something set at creation time.
        public function registerAdmin(): void
        {
            $this->requireAuthentication();
            $this->requireSuperAdmin();

            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            $errors = [];

            if ($username === '') {
                $errors[] = 'Username is required.';
            } elseif (strlen($username) < 3) {
                $errors[] = 'Username must be at least 3 characters.';
            } elseif ($this->systemManagerRepository->findManager($username) !== null) {
                $errors[] = 'That username is already taken.';
            }

            if ($password === '') {
                $errors[] = 'Password is required.';
            } elseif (strlen($password) < 8) {
                $errors[] = 'Password must be at least 8 characters.';
            }

            if ($password !== $confirmPassword) {
                $errors[] = 'Passwords do not match.';
            }

            if (!empty($errors)) {
                $_SESSION['admin_register_errors'] = $errors;
                header('Location: ../Views/Admin/adminRegister.php');
                exit;
            }

            $hash = password_hash($password, PASSWORD_DEFAULT);
            $this->systemManagerRepository->createManager($username, $hash);

            $_SESSION['admin_register_success'] = true;
            header('Location: ../Views/Admin/adminRegister.php');
            exit;
        }

        // Only reachable by the super admin, same as registerAdmin()
        // above. Two safety checks run before any actual deletion:
        // never allow removing the last remaining admin (this project
        // has no account-recovery mechanism, so that would lock
        // everyone out permanently), and never allow removing an
        // admin who's ever been attached to real data (booking,
        // machine, service, slot, or enquiry all have a NOT NULL
        // manager_id foreign key with no reassignment logic built for
        // it yet). Only a genuinely unused admin account can actually
        // be deleted.
        public function removeAdmin(): void
        {
            $this->requireAuthentication();
            $this->requireSuperAdmin();

            $managerId = (int)($_POST['manager_id'] ?? 0);

            if ($this->systemManagerRepository->countAll() <= 1) {
                $_SESSION['admin_register_errors'] = ['Cannot remove the last remaining admin account.'];
                header('Location: ../Views/Admin/adminRegister.php');
                exit;
            }

            if ($this->systemManagerRepository->hasAssociatedRecords($managerId)) {
                $_SESSION['admin_register_errors'] = ['This admin cannot be removed - they are already linked to real bookings or other records in the system.'];
                header('Location: ../Views/Admin/adminRegister.php');
                exit;
            }

            $this->systemManagerRepository->deleteManager($managerId);

            $_SESSION['admin_register_success'] = true;
            header('Location: ../Views/Admin/adminRegister.php');
            exit;
        }
    }