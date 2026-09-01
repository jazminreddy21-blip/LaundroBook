<?php
/*
    PHP added at the top of this page, the confirmation
    card now has extra rows for name, service, duration, machine,
    and slot times, pulling the values bookingController stashed in
    session.

    This page expects to be reached via a redirect from
    bookingController::confirmBooking() after a successful booking.
    Using session rather than url query params keeps the customers
    email and personal details out of the address bar/browser history.

    htmlspecialchars() is used on every value before its echoed,
    session data originated from user-submitted form input 
*/
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$bookingReference = $_SESSION['booking_reference'] ?? null;


// sends users back to the booking form instead of showing a fake "confirmed"
// receipt with placeholder values.
if ($bookingReference === null) {
    header('Location: booking.php');
    exit;
}

$customerEmail = $_SESSION['customer_email'] ?? null;
$customerName = $_SESSION['customer_name'] ?? null;
$bookingDate = $_SESSION['booking_date'] ?? null;
$washType = $_SESSION['wash_type'] ?? null;
$loadType = $_SESSION['load_type'] ?? null;
$durationMinutes = $_SESSION['duration_minutes'] ?? null;
$machineName = $_SESSION['machine_name'] ?? null;
$slotLabel = $_SESSION['slot_label'] ?? null;
$secondSlotLabel = $_SESSION['second_slot_label'] ?? null;
$collectionMethod = $_SESSION['collection_method'] ?? null;
$deliveryAddress = $_SESSION['delivery_address'] ?? null;


// If someone lands on this page directly (bookmarked it, refreshed
// after the session values were already cleared) rather than
// arriving via a real redirect, there's no booking to actually
// confirm, status reflects that instead of falsely claiming "Confirmed".
// The redirect guard above already ensures $bookingReference exists
// by this point, so status is always genuinely Confirmed here
$bookingStatus = 'Confirmed';

// Service type combines wash + load into one readable line, e.g.
// "Heavy Wash - Beddings" instead of two separate raw values.
$serviceType = ($washType && $loadType)
    ? ucfirst($washType) . ' Wash - ' . ucfirst($loadType)
    : null;

// Heavy Wash bookings occupy two slots, show both if a second one
// exists, otherwise just the one.
$slotTimes = $slotLabel;
if ($secondSlotLabel) {
    $slotTimes = $slotLabel . ' & ' . $secondSlotLabel;
}

$bookingReference = htmlspecialchars($bookingReference, ENT_QUOTES, 'UTF-8');
$customerEmail = $customerEmail ? htmlspecialchars($customerEmail, ENT_QUOTES, 'UTF-8') : 'Not Available';
$customerName = $customerName ? htmlspecialchars($customerName, ENT_QUOTES, 'UTF-8') : 'Not Available';
$bookingDate = $bookingDate ? htmlspecialchars($bookingDate, ENT_QUOTES, 'UTF-8') : 'Not Available';
$serviceType = $serviceType ? htmlspecialchars($serviceType, ENT_QUOTES, 'UTF-8') : 'Not Available';
$durationMinutes = $durationMinutes ? htmlspecialchars($durationMinutes . ' minutes', ENT_QUOTES, 'UTF-8') : 'Not Available';
$machineName = $machineName ? htmlspecialchars($machineName, ENT_QUOTES, 'UTF-8') : 'Not Available';
$slotTimes = $slotTimes ? htmlspecialchars($slotTimes, ENT_QUOTES, 'UTF-8') : 'Not Available';

// Only relevant for delivery bookings, pickup customers have no
// address on file for this booking, so nothing gets shown for them.
$showDeliveryAddress = ($collectionMethod === 'delivery');
$deliveryAddress = $deliveryAddress ? htmlspecialchars($deliveryAddress, ENT_QUOTES, 'UTF-8') : 'Not Available';
?>
<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Booking Confirmed | LaundroBook</title>

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

<a href="index.html" class="logo"> <i class="fa-solid fa-shirt"></i>

<div class="logo-text">
    Laundro<span>Book</span>
</div>

</a>

<nav>

<ul>

<li><a href="index.html">Home</a></li> <li><a href="about.html">About</a></li> <li><a href="contact.html">Contact</a></li>

</ul>

</nav>

</div>

</header>




<!-- Booking Confirmation Section -->

<section class="tracking-section">

<div class="container">

<div class="section-header">

<div class="section-subtitle"> Booking Successful </div>

<h2 class="section-title"> Your Laundry Booking Has Been Confirmed! </h2>

<p class="section-desc">

Thank you for choosing LaundroBook. Your booking has been successfully processed.

</p>

</div>

<br>
<br>

<!-- Confirmation Card -->

<div class="tracking-card">

<h3>Booking Confirmation</h3>

<p>

<strong>Full Name:</strong>

<span id="customer-name">

<?php echo $customerName; ?>

</span>

</p>

<p>

<strong>Booking Reference Number:</strong>

<!-- PHP: Display the unique booking reference number generated after the booking has been successfully saved. -->

<span id="booking-reference">

<?php echo $bookingReference; ?>

</span>

</p>

<p>

<strong>Email Address:</strong>

<!-- PHP: Display the customer's email address used when making the booking. -->

<span id="customer-email">

<?php echo $customerEmail; ?>

</span>

</p>

<p>

<strong>Booking Date:</strong>

<span id="booking-date">

<?php echo $bookingDate; ?>

</span>

</p>

<p>

<strong>Service Type:</strong>

<span id="service-type">

<?php echo $serviceType; ?>

</span>

</p>

<p>

<strong>Duration:</strong>

<span id="service-duration">

<?php echo $durationMinutes; ?>

</span>

</p>

<p>

<strong>Machine:</strong>

<span id="machine-name">

<?php echo $machineName; ?>

</span>

</p>

<p>

<strong>Time Slot:</strong>

<span id="slot-times">

<?php echo $slotTimes; ?>

</span>

</p>

<?php if ($showDeliveryAddress): ?>
<p>

<strong>Delivery Address:</strong>

<span id="delivery-address">

<?php echo $deliveryAddress; ?>

</span>

</p>
<?php endif; ?>

<p>

<strong>Booking Status:</strong>

<!-- PHP: Display the customer's booking status. Example: Confirmed -->

<span id="booking-status">

<?php echo $bookingStatus; ?>

</span>

</p>

</div>

<br>
<br>

<!-- Confirmation Message Card -->

<div class="tracking-card">

<h3>What's Next?</h3>

<p>

A confirmation email containing your booking details and booking reference number has been sent to your registered email address.

</p>

<p>

Please keep your booking reference number safe, as it will be required when tracking your laundry order.

</p>

</div>

<br>
<br>

<!-- Tracking Options Card -->

<div class="tracking-card">

<h3>Track Your Laundry</h3>

<p>

You can track the progress of your laundry at any time using your booking reference number.

</p>

<div class="nav-buttons">

<a href="TrackingDel.html" class="btn btn-secondary">

Track Delivery

</a>

<a href="TrackingPick.html" class="btn btn-primary">

Track Pickup

</a>

</div>

</div>

<br>
<br>

<!-- Return Home Card -->

<div class="tracking-card">

<h3>Return Home</h3>

<p>

Thank you for booking with LaundroBook. We look forward to making laundry day easier for you!

</p>

<div class="nav-buttons">

<a href="index.html" class="btn btn-primary">

Return Home

</a>

</div>

</div>

</div>

</section>

</body>

</html>