<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../Interfaces/Repositoryinterfaces.php';
require_once __DIR__ . '/../Repositories/SystemManagerRepo.php';
require_once __DIR__ . '/../Controllers/AdminController.php';

// This is the single entry point every admin management page's
// status-update / reassignment forms post to. Dispatches purely on
// $_POST['action'] - each handler method re-checks authentication
// itself, so this file doesn't need to.
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {

    $controller = new AdminController(new SystemManagerRepo());

    $action = trim($_POST['action'] ?? '');

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

        case 'reassign_groundworker':
            $controller->reassignGroundworker();
            break;

        case 'update_enquiry_status':
            $controller->updateEnquiryStatusAction();
            break;

        default:
            http_response_code(400);
            echo 'Unknown action.';
            break;
    }
}