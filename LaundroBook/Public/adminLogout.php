<?php

session_start();

require_once __DIR__ . '/../Interfaces/RepositoryInterfaces.php';
require_once __DIR__ . '/../Repositories/SystemManagerRepo.php';
require_once __DIR__ . '/../Controllers/AdminController.php';


$repository = new SystemManagerRepo();

$controller = new AdminController($repository);

$controller->logout();



