<?php
    require_once __DIR__ . '/../Interfaces/Repositoryinterfaces.php'; 
    require_once __DIR__ . '/../Repositories/SystemManagerRepo.php'; 

    class AdminController{
        //validate the inputs on the server side by checking the database 
        //once validated successfully, then we can route the request to the system manager repo
        //which will then fetch the admin from the database and return a result if it fails
        private SystemManagerRepoInterface $systemManagerRepository; 

        public function __construct(SystemManagerRepoInterface $systemManagerRepository){
            $this->systemManagerRepository = $systemManagerRepository;  
        }

        public function showLogin(){
            require_once __DIR__ . '/../Views/login.php'; 
        }


        public function login() {

            if($_SERVER['REQUEST_METHOD'] !== 'POST'){
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

            header('Location: Views/login.html');
            exit;
        }
        public function requireAuthentication(){
            if(!isset($_SESSION['manager_id'])){
                header('Location: /login.html'); 
                exit; 
            }
        }
    }



/*
<!--
Backend Developer Notes:

1. Use password hashing.
2. Do NOT store plain text passwords.
3. Create admin session after successful login.
4. Prevent access to dashboard pages without session.
5. Redirect unauthenticated users to login page.
-->

*/