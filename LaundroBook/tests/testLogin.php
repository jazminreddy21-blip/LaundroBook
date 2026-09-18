<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "TEST STARTED<br>";

require_once __DIR__ . '/../Interfaces/Repositoryinterfaces.php';
echo "Interface loaded<br>";

require_once __DIR__ . '/../Repositories/SystemManagerRepo.php';
echo "Repository loaded<br>";

require_once __DIR__ . '/../Controllers/AdminController.php';
echo "Controller loaded<br>";

$username = "Mabutho";
$password = "Tpnidy3355";

echo "Creating repository<br>";

$systemManagerRepo = new SystemManagerRepo();

echo "Repository created<br>";

$adminController = new AdminController($systemManagerRepo);

echo "Controller created<br>";

$result = $adminController->login($username, $password);

echo "Login method finished<br>";

var_dump($result);
