<?php

// Single entry point for the small write actions the admin pages

session_start();

require_once __DIR__ . '/../Interfaces/Repositoryinterfaces.php';
require_once __DIR__ . '/../Repositories/SystemManagerRepo.php';
require_once __DIR__ . '/../Controllers/AdminController.php';

$repository = new SystemManagerRepo();
$controller = new AdminController($repository);

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'update_booking_status':
        $controller->updateBookingStatus();
        break;

    case 'update_machine_status':
        $controller->updateMachineStatusAction();
        break;

    case 'update_delivery_status':
        $controller->updateDeliveryStatusAction();
        break;

    default:
        http_response_code(400);
        echo 'Unknown admin action.';
}
