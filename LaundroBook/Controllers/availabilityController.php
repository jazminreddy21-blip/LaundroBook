<?php

require_once __DIR__ . '/../Interfaces/Repositoryinterfaces.php';
require_once __DIR__ . '/../Services/AvailabilityService.php';
require_once __DIR__ . '/../Repositories/MachineRepo.php';
require_once __DIR__ . '/../Repositories/SlotRepo.php';
require_once __DIR__ . '/../Repositories/BookingRepo.php';

//  AvailabilityController
//  this is what the "Book Now" button calls, before the customer has been created or
//  confirmed as anything. Deliberately separate from BookingController.
//  Safe to expose without auth: never touches customer, never inserts
//  anything. 
class AvailabilityController
{
    private AvailabilityService $availabilityService;

    public function __construct()
    {
        $this->availabilityService = new AvailabilityService(
            new MachineRepo(),
            new SlotRepo(),
            new BookingRepo()
        );
    }

    //  Expects POST: booking_date, duration_slots
    //  note to self, (duration_slots comes from a ServiceRepository::findByType lookup
    //  on the frontend/JS side, or can be looked up here too if preferred)
    public function checkAvailability(): void
    {
        header('Content-Type: application/json');

        $bookingDate = $_POST['booking_date'] ?? '';
        $durationSlots = (int)($_POST['duration_slots'] ?? 1);

        if (empty($bookingDate) || !$this->isValidDate($bookingDate)) {
            http_response_code(400);
            echo json_encode(['error' => 'A valid booking date is required']);
            return;
        }

        $combos = $this->availabilityService->getAvailableCombos($bookingDate, $durationSlots);

        echo json_encode([
            'booking_date' => $bookingDate,
            'available' => $combos,
        ]);
    }

    private function isValidDate(string $date, string $format = 'Y-m-d'): bool
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action']) && $_POST['action'] === 'check_availability') {
    $controller = new AvailabilityController();
    $controller->checkAvailability();
}