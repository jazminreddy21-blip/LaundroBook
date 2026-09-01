<?php

/*
    test.php

    Hardcoded stand-in for AvailabilityController::checkAvailability().
    Returns the exact same JSON shape the real endpoint will eventually
    return, so booking.js can be built and tested against it right now,
    with zero database connection needed.

    SWAP LATER: once MachineRepo/SlotRepo/BookingRepo are wired to a real
    database, change the fetch URL in booking.js from this file to the
    real AvailabilityController.php (action=check_availability), the
    JSON shape below is designed to match exactly, so nothing else in
    booking.js should need to change.
*/

header('Content-Type: application/json');

$bookingDate = $_POST['booking_date'] ?? '';
$durationSlots = (int)($_POST['duration_slots'] ?? 1);

if (empty($bookingDate)) {
    http_response_code(400);
    echo json_encode(['error' => 'A valid booking date is required']);
    exit;
}

// Hardcoded fake machines and slots, pretend machine 2 is fully booked
// today, and machine 1's slot 2 is taken, so you can watch the select
// options actually change based on what "duration_slots" gets sent.
if ($durationSlots === 1) {
    $available = [
        ['machine_id' => 1, 'machine_name' => 'Machine 1', 'slot_id' => 1, 'slot_label' => '08:00 - 08:45'],
        ['machine_id' => 1, 'machine_name' => 'Machine 1', 'slot_id' => 3, 'slot_label' => '09:30 - 10:15'],
        ['machine_id' => 3, 'machine_name' => 'Machine 3', 'slot_id' => 1, 'slot_label' => '08:00 - 08:45'],
        ['machine_id' => 3, 'machine_name' => 'Machine 3', 'slot_id' => 2, 'slot_label' => '08:45 - 09:30'],
        ['machine_id' => 3, 'machine_name' => 'Machine 3', 'slot_id' => 3, 'slot_label' => '09:30 - 10:15'],
    ];
} 
else {
    // Heavy Wash, only combos with a valid consecutive second slot
    $available = [
        [
            'machine_id' => 3, 'machine_name' => 'Machine 3',
            'slot_id' => 1, 'slot_label' => '08:00 - 08:45',
            'second_slot_id' => 2,
        ],
        [
            'machine_id' => 3, 'machine_name' => 'Machine 3',
            'slot_id' => 2, 'slot_label' => '08:45 - 09:30',
            'second_slot_id' => 3,
        ],
    ];
}

echo json_encode([
    'booking_date' => $bookingDate,
    'available' => $available,
]);