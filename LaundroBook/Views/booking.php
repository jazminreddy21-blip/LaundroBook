<?php
/*
    Converted from booking.html to booking.php, this way the
    bookingController session-stashed errors can reach this
    page and be displayed on load

    #validationMessage class and contents are set here from
    session data, instead of always starting empty
*/
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$bookingErrors = $_SESSION['booking_errors'] ?? [];
unset($_SESSION['booking_errors']);

// Clears any leftover receipt data from a previous confirmed booking.
unset(
    $_SESSION['booking_reference'], $_SESSION['customer_email'],
    $_SESSION['customer_name'], $_SESSION['booking_date'],
    $_SESSION['wash_type'], $_SESSION['load_type'],
    $_SESSION['duration_minutes'], $_SESSION['machine_name'],
    $_SESSION['slot_label'], $_SESSION['second_slot_label'],
    $_SESSION['collection_method'], $_SESSION['delivery_address']
);

$hasErrors = !empty($bookingErrors);

// Builds the exact same markup structure booking.js already generates
// client-side for validation errors (<ul><li>...), so the CSS rule
// .validation-message.error applies identically whether the error came
// from JS on the client or from PHP on page reload.
$errorsHtml = '';
if ($hasErrors) {
    $errorsHtml .= '<ul>';
    foreach ($bookingErrors as $error) {
        $errorsHtml .= '<li>' . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . '</li>';
    }
    $errorsHtml .= '</ul>';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book a Wash | LaundroBook</title>

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
            <a href="index.html" class="logo">
                <i class="fa-solid fa-shirt"></i>
                <div class="logo-text">
                    Laundro<span>Book</span>
                </div>
            </a>

            <nav>
                <ul>
                    <li><a href="index.html">Home</a></li>
                    <li><a href="about.html">About</a></li>
                    <li><a href="contact.html">Contact</a></li>
                </ul>
            </nav>

            <div class="nav-buttons">
                <a href="TrackingDel.html" class="btn btn-secondary">Track Delivery</a>
                <a href="TrackingPick.html" class="btn btn-primary">Track Pickup</a>
            </div>
        </div>
    </header>

    <!-- Booking Section -->
    <section class="booking-section">
        <div class="container">

            <div class="section-header">
                <div class="section-subtitle">Reserve Your Laundry Slot</div>
                <h2 class="section-title">Book Your Washing Machine</h2>
                <p class="section-desc">
                    Reserve your preferred washing machine and select the service that best suits your needs.
                </p>
            </div>

            <!-- CHANGED: was action="#", now posts to the real controller/testing controller -->
            <form action="../tests/Mock confirm booking.php" method="POST" class="booking-form" id="bookingForm" novalidate>

                <!-- CHANGED: class/contents now set from $_SESSION['booking_errors']
                     on page load, in addition to being set by booking.js on the
                     client side during normal use. -->
                <div id="validationMessage" class="validation-message<?php echo $hasErrors ? ' error' : ''; ?>">
                    <?php echo $errorsHtml; ?>
                </div>

                <!-- =========================================================
                     CUSTOMER INFORMATION SECTION
                ========================================================= -->
                <section class="booking-details">

                    <h3>Customer Information</h3>
                    <p>Please enter your contact details before making your booking.</p>

                    <label for="customer_name">Full Name</label>
                    <input
                        type="text"
                        id="customer_name"
                        name="customer_name"
                        placeholder="Enter your full name"
                        autocomplete="name"
                        required>

                    <label for="customer_email">Email Address</label>
                    <input
                        type="email"
                        id="customer_email"
                        name="customer_email"
                        placeholder="Enter your email address"
                        autocomplete="email"
                        required>

                    <label for="customer_phone">Phone Number</label>
                    <input
                        type="tel"
                        id="customer_phone"
                        name="customer_phone"
                        placeholder="Enter your phone number"
                        inputmode="numeric"
                        autocomplete="tel"
                        required>

                </section>

                <!-- =========================================================
                     BOOKING INFORMATION SECTION
                ========================================================= -->
                <section class="booking-details">

                    <h3>Booking Information</h3>
                    <p>Select your preferred date, service, and collection method.</p>

                    <label for="booking_date">Booking Date</label>
                    <input type="date" name="booking_date" id="booking_date" required>

                    <label for="wash_type">Wash Type</label>
                    <select name="wash_type" id="wash_type" required>
                        <option value="">Select Wash Type</option>
                        <option value="quick">Quick (25 Min)</option>
                        <option value="normal">Normal (35 Min)</option>
                        <option value="heavy">Heavy (65 Min)</option>
                    </select>

                    <label for="load_type">Load Type</label>
                    <select name="load_type" id="load_type" required>
                        <option value="">Select Load Type</option>
                        <option value="clothes">Clothes</option>
                        <option value="beddings">Beddings</option>
                        <option value="towels">Towels</option>
                    </select>

                    <label for="collection_method">Collection Method</label>
                    <select name="collection_method" id="collection_method" required>
                        <option value="">Select Collection Method</option>
                        <option value="pickup">Self Pickup</option>
                        <option value="delivery">Home Delivery</option>
                    </select>

                    <div id="addressSection" class="hidden">
                        <label for="address">Delivery Address</label>
                        <textarea name="delivery_address" id="address"
                            placeholder="Enter delivery address"></textarea>
                    </div>

                </section>

                <!-- Book Now Button -->
                <div class="booking-actions">
                    <button type="button" id="booking_button" class="btn btn-primary">
                        Book Now
                    </button>
                </div>

                <!-- =========================================================
                     AVAILABILITY SECTION
                ========================================================= -->
                <section id="availabilitySection" class="availability-section hidden">

                    <div class="availability-header">
                        <h3>Booking Details</h3>
                        <p>Your service details have been confirmed. Please select an available machine and time slot.</p>
                    </div>

                    <!-- Service Summary -->
                    <div class="service-result">
                        <h4>Selected Service</h4>
                        <div class="service-result-grid">

                            <div class="service-result-item">
                                <span>Wash Type</span>
                                <strong id="selectedWashType">-</strong>
                            </div>

                            <div class="service-result-item">
                                <span>Load Type</span>
                                <strong id="selectedLoadType">-</strong>
                            </div>

                            <div class="service-result-item">
                                <span>Price</span>
                                <strong>R<span id="servicePrice">0.00</span></strong>
                            </div>

                            <div class="service-result-item">
                                <span>Duration</span>
                                <strong id="serviceDuration">-</strong>
                            </div>

                        </div>
                    </div>

                    <!-- Booking Date Display -->
                    <div class="booking-date-result">
                        <span>Booking Date</span>
                        <strong id="selectedBookingDate">-</strong>
                    </div>

                    <!-- Machine and Slot Selection -->
                    <div class="availability-grid">

                        <div class="form-group">
                            <label for="machineSelect">Available Washing Machine</label>
                            <select id="machineSelect" name="machine_id">
                                <option value="">Select an available machine</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="slotSelect">Available Time Slot</label>
                            <select id="slotSelect" name="slot_id">
                                <option value="">Select an available time slot</option>
                            </select>
                        </div>
                        <input type="hidden" id="secondSlotId" name="second_slot_id" value="">
                    </div>

                    <!-- Collection Method Display -->
                    <div class="booking-date-result">
                        <span>Collection Method</span>
                        <strong id="selectedCollectionMethod">-</strong>
                    </div>

                    <!-- Confirm Booking Button -->
                    <div class="availability-actions">
                        <button type="submit" id="confirmBookingBtn" class="btn btn-primary">
                            Confirm Booking
                        </button>
                    </div>

                </section>

            </form>

        </div>
    </section>

    <script src="../JS/booking.js"></script>
</body>
</html>