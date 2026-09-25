<?php
require_once __DIR__ . '/../Interfaces/Repositoryinterfaces.php';
require_once __DIR__ . '/../Repositories/BookingRepo.php';
require_once __DIR__ . '/../Repositories/DeliveryRepo.php';
require_once __DIR__ . '/../Repositories/ServiceRepo.php';
require_once __DIR__ . '/../Repositories/MachineRepo.php';
require_once __DIR__ . '/../Repositories/SlotRepo.php';
require_once __DIR__ . '/../Controllers/TrackingController.php';

$controller = new TrackingController(new BookingRepo(), new DeliveryRepo(), new ServiceRepo(), new MachineRepo(), new SlotRepo());

$result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['booking_reference'])) {
    $result = $controller->track($_POST['booking_reference'], 'delivery');
}

// Six named steps for the visual tracker, but only three real
// delivery_status values exist in the database (pending / in_progress
// / completed - see the CHECK constraint). Rather than pretending
// each step is independently trackable, each real status lights up a
// BLOCK of consecutive steps together, since "in_progress" genuinely
// covers a range of real-world sub-stages we can't distinguish yet
// with the data available - pending lights the first step only,
// in_progress lights the next three as one block, completed lights
// everything.
$stepLabels = ['Order Confirmed', 'Laundry Completed', 'Ready For Delivery', 'Driver Assigned', 'Out For Delivery', 'Successfully Delivered'];

// How many of the 6 steps light up for each real status, and a richer
// caption than the bare status word - shown in the Delivery Status
// card so a customer sees something more specific than just
// "In progress".
$statusInfo = [
    'pending' => ['lit' => 1, 'caption' => 'Order Confirmed - Preparing Your Laundry'],
    'in_progress' => ['lit' => 4, 'caption' => 'Processing - Awaiting Driver Assignment'],
    'completed' => ['lit' => 6, 'caption' => 'Successfully Delivered'],
];

$currentStatus = ($result !== null && !isset($result['error']))
    ? strtolower($result['delivery']['delivery_status'])
    : null;
$litCount = $currentStatus !== null ? $statusInfo[$currentStatus]['lit'] : 0;
$statusCaption = $currentStatus !== null ? $statusInfo[$currentStatus]['caption'] : '';
?>
<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Track Delivery | LaundroBook</title>

<!-- Font Awesome -->
<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

<!-- CSS -->
<link rel="stylesheet" href="../CSS/style.css">

</head>

<body>

<!-- Header -->

<header>

<div class="container header-container">

<a href="index.html" class="logo">  <img src="../Images/logo.png" alt="LaundroBook Logo" class="logo-image">

<div class="logo-text">
    Laundro<span>Book</span>
</div>

</a>

<nav>

<ul>

<li><a href="index.html">Home</a></li> <li><a href="about.html">About</a></li> <li><a href="contact.php">Contact</a></li>

</ul>

</nav>

<div class="nav-buttons">

<a href="booking.php" class="btn btn-secondary">

Book Now

</a>

<a href="TrackingPick.php" class="btn btn-primary">

Track Pickup

</a>

<a href="TrackingOrder.php" id= "track-pickup-btn" class=" btn btn-primary">

Track Order

</a>

</div>

</div>

</header>

<!-- Tracking Section -->

<section class="tracking-section">

<div class="container">

<div class="section-header">

<div class="section-subtitle"> Track Your Laundry </div>

<h2 class="section-title"> Delivery Tracking </h2>

<p class="section-desc">

Stay updated on the status of your laundry delivery in real time.

</p>

</div>

<!-- Tracking Form -->

<form action="TrackingDel.php" method="POST" class="tracking-form">

<label for="booking_reference">

Booking Reference Number

</label>

<input type="text" name="booking_reference" id="booking_reference"
       placeholder="Enter your booking reference number"
       value="<?= htmlspecialchars($_POST['booking_reference'] ?? ''); ?>"
       required>

<button type="submit" class="btn btn-primary">

Track Order

</button>

</form>

<?php if ($result !== null): ?>
<?php if (isset($result['error'])): ?>

<div class="validation-message error" style="max-width:600px;margin:20px auto 0;">
    <p><?= htmlspecialchars($result['error']); ?></p>
</div>

<?php else: ?>

<!-- Tracking Results -->

<div class="tracking-results">

<!-- Booking Details Card -->

<div class="tracking-card">

<h3>Booking Details</h3>

<p> <strong>Booking Reference:</strong>
<span id="tracking-reference"><?= htmlspecialchars($result['booking']['booking_reference']); ?></span>
</p>

<p> <strong>Laundry Service:</strong>
<span id="tracking-service"><?= htmlspecialchars(ucfirst($result['service']['wash_type']) . ' Wash - ' . ucfirst($result['service']['load_type'])); ?></span>
</p>

<p> <strong>Collection Method:</strong>
<span id="tracking-collection"><?= htmlspecialchars($result['collection_method']); ?></span>
</p>

</div>

<!-- Delivery Status Card -->

<div class="tracking-card">

<h3>Delivery Status</h3>

<p> <strong>Current Status:</strong>
<span id="tracking-status"><?= htmlspecialchars($statusCaption); ?></span>
</p>

<p> <strong>Estimated Delivery Time:</strong>
<span id="tracking-time"><?= htmlspecialchars(date('d M Y, H:i', strtotime($result['delivery']['scheduled_time']))); ?></span>
</p>

<p> <strong>Assigned Driver:</strong>
<span id="tracking-driver"><?= htmlspecialchars($result['delivery']['groundworker_name']); ?></span>
</p>

</div>

<!-- Laundry Progress Card -->

<div class="tracking-card">

<h3>Delivery Progress</h3>

<ul class="delivery-progress">

    <?php foreach ($stepLabels as $i => $label): ?>
        <li class="<?= $i < $litCount ? 'active' : ''; ?>">
            <?= htmlspecialchars($label); ?>
        </li>
    <?php endforeach; ?>

</ul>

</div>

<!-- Driver Information -->

<div class="driver-info-card" id="driverInfoCard">

    <h2>Driver Information</h2>

    <div class="driver-details">

        <p>
            <strong>Driver Name:</strong>
            <span id="driverName"><?= htmlspecialchars($result['delivery']['groundworker_name']); ?></span>
        </p>

        <p>
            <strong>Contact Number:</strong>
            <span id="driverPhone"><?= htmlspecialchars($result['delivery']['groundworker_phone']); ?></span>
        </p>

        <p>
            <strong>Estimated Arrival:</strong>
            <span id="estimatedArrival"><?= htmlspecialchars(date('d M Y, H:i', strtotime($result['delivery']['scheduled_time']))); ?></span>
        </p>

    </div>

</div>

</div>

<?php endif; ?>
<?php endif; ?>

</div>

</section>

</body>

</html>