<?php
/*
    Converted from contact.html to contact.php, matching booking.php's
    pattern - session-stashed errors from enquiryController need a PHP
    page to read and display them on reload.
*/
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$enquiryErrors = $_SESSION['enquiry_errors'] ?? [];
unset($_SESSION['enquiry_errors']);

$enquirySuccess = $_SESSION['enquiry_success'] ?? false;
unset($_SESSION['enquiry_success']);

$hasErrors = !empty($enquiryErrors);

$errorsHtml = '';
if ($hasErrors) {
    $errorsHtml .= '<ul>';
    foreach ($enquiryErrors as $error) {
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

    <title>Contact | LaundroBook</title>

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

    <!-- CSS -->
    <link rel="stylesheet" href="../CSS/style.css">

</head>

<body>

<!-- ================= HEADER ================= -->

<header>

<div class="container header-container">

    <!-- Logo -->

    <a href="index.html" class="logo">

       <img src="../Images/logo.png" alt="LaundroBook Logo" class="logo-image"> 

        <div class="logo-text">
            Laundro<span>Book</span>
        </div>

    </a>

    <!-- Navigation -->

    <nav>

        <ul>

            <li><a href="index.html">Home</a></li>

            <li><a href="about.html">About</a></li>

            <li><a href="contact.php" class="active">Contact</a></li>

        </ul>

    </nav>

    <!-- Navigation Buttons -->

    <div class="nav-buttons">

        <a href="TrackingDel.html"
        id="track-delivery-btn"
        class="btn btn-secondary">

            Track Laundry Delivery

        </a>

        <a href="TrackingPick.html"
        id="track-progress-btn"
        class="btn btn-primary">

            Track Progress for Pickup

        </a>

    </div>

</div>

</header>


<!-- ================= CONTACT SECTION ================= -->

<section class="contact">

<div class="container">

    <div class="section-header">

        <div class="section-subtitle">

            Contact Us

        </div>

        <h2 class="section-title">

            We're Here To Help

        </h2>

        <p class="section-desc">

            Have questions about bookings, deliveries or pickups?
            Get in touch with our team today.

        </p>

    </div>



    <div class="contact-container">


        <!-- ================= CONTACT INFORMATION ================= -->

        <div class="contact-info">

            <h3>Get In Touch</h3>

            <p>
                <i class="fa-solid fa-phone"></i>
                +27 66 289 8213
            </p>

            <p>
                <i class="fa-solid fa-envelope"></i>
                laundrobook@gmail.com
            </p>

            <p>
                <i class="fa-solid fa-location-dot"></i>
                Durban, South Africa
            </p>

            <p>
                <i class="fa-solid fa-clock"></i>
                Mon - Sun | 08:00 - 19:30
            </p>

        </div>



        <!-- ================= CONTACT FORM ================= -->

        <div class="contact-form">

            <?php if ($enquirySuccess): ?>
            <div class="validation-message success">
                <p>Thank you - your message has been sent. We'll get back to you soon.</p>
            </div>
            <?php endif; ?>

            <!-- CHANGED: class/contents now set from
                 $_SESSION['enquiry_errors'] on page load, same pattern
                 as booking.php's validationMessage div. -->
            <div id="validationMessage" class="validation-message<?php echo $hasErrors ? ' error' : ''; ?>">
                <?php echo $errorsHtml; ?>
            </div>

            <!-- CHANGED: was action="contact-process.php", now posts to
                 the real controller, matching bookingController.php's
                 relative path and lowercase-first-letter naming. -->
            <form
            id="contact-form"
            action="../Controllers/enquiryController.php"
            method="POST">


                <!-- Full Name -->

                <input
                type="text"
                id="full-name"
                name="full_name"
                placeholder="Your Full Name"
                maxlength="50"
                >



                <!-- Email Address -->

                <input
                type="email"
                id="email-address"
                name="email_address"
                placeholder="Email Address"
                maxlength="100"
                >



                <!-- Subject -->

                <input
                type="text"
                id="message-subject"
                name="message_subject"
                placeholder="Subject"
                maxlength="100"
                >



                <!-- Customer Message -->

                <textarea
                id="customer-message"
                name="customer_message"
                placeholder="Enter your message here..."
                rows="6"
                maxlength="500"
                >

                </textarea>



                <!-- Submit Button -->

                <button
                type="submit"
                id="send-message-btn"
                class="btn btn-primary">

                  Submit

                </button>


            </form>

        </div>

    </div>

</div>

</section>

<script src="../JS/contact.js"></script>
</body>
</html>