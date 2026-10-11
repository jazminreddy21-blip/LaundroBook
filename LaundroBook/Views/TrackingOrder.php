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
    $result = $controller->trackOrder($_POST['booking_reference']);
}


$stepLabels = ['Order Confirmed', 'Wash In Progress', 'Ready For Collection'];
$statusInfo = [
    'pending' => ['lit' => 1, 'caption' => 'Order Confirmed - Awaiting Wash'],
    'in_progress' => ['lit' => 2, 'caption' => 'Your Laundry Is Being Washed'],
    'completed' => ['lit' => 3, 'caption' => 'Ready For Collection'],
    'cancelled' => ['lit' => 0, 'caption' => 'This Booking Was Cancelled'],
];

$currentStatus = ($result !== null && !isset($result['error']))
    ? strtolower($result['booking']['status'])
    : null;
$litCount = $currentStatus !== null ? $statusInfo[$currentStatus]['lit'] : 0;
$statusCaption = $currentStatus !== null ? $statusInfo[$currentStatus]['caption'] : '';
$isCancelled = $currentStatus === 'cancelled';
?>
<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Track Your Order | LaundroBook</title>

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

<link rel="stylesheet" href="../CSS/style.css">

</head>

<body>

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

</div>

</div>

</header>

<section class="tracking-section">

<div class="container">

<div class="section-header">

<div class="section-subtitle"> Track Your Laundry </div>

<h2 class="section-title"> Order Tracking </h2>

<p class="section-desc">

Check the status of your wash - works for every booking, whether you chose delivery or self drop-off.

</p>

</div>

<form action="TrackingOrder.php" method="POST" class="tracking-form">

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

<div class="tracking-results">

<div class="tracking-card">

<h3>Booking Details</h3>

<p> <strong>Booking Reference:</strong>
<span><?= htmlspecialchars($result['booking']['booking_reference']); ?></span>
</p>

<p> <strong>Laundry Service:</strong>
<span><?= htmlspecialchars(ucfirst($result['service']['wash_type']) . ' Wash - ' . ucfirst($result['service']['load_type'])); ?></span>
</p>

<p> <strong>Booking Date:</strong>
<span><?= htmlspecialchars(date('d M Y', strtotime($result['booking']['booking_date']))); ?></span>
</p>

</div>

<div class="tracking-card">

<h3>Wash Status</h3>

<p> <strong>Current Status:</strong>
<span><?= htmlspecialchars($statusCaption); ?></span>
</p>

<p> <strong>Machine:</strong>
<span><?= htmlspecialchars($result['machine']['machine_name'] ?? 'Not assigned'); ?></span>
</p>

<p> <strong>Time Slot:</strong>
<span><?= htmlspecialchars($result['slot']['slot_label'] ?? 'Not available'); ?></span>
</p>

</div>

<div class="tracking-card">

<h3>Wash Progress</h3>

<?php if ($isCancelled): ?>

    <p style="padding:8px 0;color:#DC2626;font-weight:600;">
        This booking was cancelled - there is no wash progress to display.
    </p>

<?php else: ?>

<ul class="delivery-progress">

    <?php foreach ($stepLabels as $i => $label): ?>
        <li class="<?= $i < $litCount ? 'active' : ''; ?>">
            <?= htmlspecialchars($label); ?>
        </li>
    <?php endforeach; ?>

</ul>

<?php endif; ?>

</div>

<?php if ($result['has_delivery'] || $result['has_collection']): ?>
<div class="tracking-card">

<h3>Delivery & Pickup</h3>

<p style="display:block;padding:8px 0;">
    This booking also has home pickup and delivery arranged.
</p>

<div class="confirmation-buttons">
    <?php if ($result['has_collection']): ?>
        <a href="TrackingPick.php" class="btn btn-primary">Track Your Pickup</a>
    <?php endif; ?>
    <?php if ($result['has_delivery']): ?>
        <a href="TrackingDel.php" class="btn btn-primary">Track Your Delivery</a>
    <?php endif; ?>
</div>

</div>
<?php endif; ?>

</div>

<?php endif; ?>
<?php endif; ?>

</div>

</section>

</body>

</html>