<?php
    require_once __DIR__. "/../controllers/BookingController.php"; 
    require_once __DIR__ . '/../repositories/SlotRepository.php';
    require_once __DIR__ . '/../repositories/MachineRepository.php';
    require_once __DIR__ . '/../repositories/BookingRepository.php';
    require_once __DIR__ . '/../repositories/ServiceRepository.php';
    require_once __DIR__ . '/../repositories/SystemManagerRepository.php';
    require_once __DIR__ . '/../repositories/CustomerRepository.php';

    

    //then we can create an instance of a booking controller here
    //and then use the method that lists all the available machines
    //before using html to display everything in this page
    $slotRepository = new SlotRepository(); // real class, hardcoded body for now
    $machineRepository = new MachineRepository(); 
    $customerRepository = new CustomerRepository(); 
    $serviceRepository = new ServiceRepository(); 
    $systemManagerRepository = new SystemManagerRepository(); 
    $bookingRepository = new BookingRepository();

    $bookingController = new BookingController($slotRepository, $machineRepository, 
    $customerRepository, $bookingRepository, $serviceRepository, 
    $systemManagerRepository);

    $availableSlots = $bookingController->availableSlots(); 
    $availableMachines = $bookingController->availableMachines(); 
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
            <form action="/LaundroBook/LaundroBook/LaundroBook/controllers/BookingController.php" method="POST" class="booking-form" id="bookingForm" novalidate>
                <div id="validationMessage" class="validation-message"></div>

                <section class="booking-details">

                    <h3>Customer Information</h3>
                    <p>Please enter your contact details before making your booking.</p>

                    <!-- PHP: Required. Min 2 characters. Strip HTML tags. -->
                    <label for="customer_name">Full Name</label>
                    <input
                        type="text"
                        id="customer_name"
                        name="customer_name"
                        placeholder="Enter your full name"
                        autocomplete="name"
                        required>

                    <!-- PHP: Required. Valid email format. Unique identifier in Customer table. -->
                    <label for="customer_email">Email Address</label>
                    <input
                        type="email"
                        id="customer_email"
                        name="customer_email"
                        placeholder="Enter your email address"
                        autocomplete="email"
                        required>

                    <!-- PHP: Required. Numbers only. 10 digits for SA phone numbers. -->
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
                        <option value="bedding">Bedding</option>
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

                
                <div class="booking-actions">
                    <button type="button" id="booking_button" class="btn btn-primary">
                        Book Now
                    </button>
                </div>

                
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
                    <!-- JS populates from #booking_date -->
                    <div class="booking-date-result">
                        <span>Booking Date</span>
                        <strong id="selectedBookingDate">-</strong>
                    </div>


                    <div class="availability-grid">
                             <div class="form-group">
                            <label for="machineSelect">Available Washing Machine</label>
                            <select id="machineSelect" name="machine_id">
                                <option value="">Select an available machine</option>
                                <?php foreach ($availableMachines as $machine): ?>
                                <option value="<?php echo htmlspecialchars($machine); ?>">
                                <?php echo htmlspecialchars($machine); ?>
                                </option>
                                <?php endforeach; ?>
                                <!-- Backend populates available machines here -->
                            </select>
                        </div>

                        
                        <div class="form-group">
                            <label for="slotSelect">Available Time Slot</label>
                            <select id="slotSelect" name="slot_id">
                                <option value="">Select an available time slot</option>
                                <?php foreach ($availableSlots as $slot): ?>
                                <option value="<?php echo htmlspecialchars($slot); ?>">
                                <?php echo htmlspecialchars($slot); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                    </div>

                    <div class="booking-date-result">
                        <span>Collection Method</span>
                        <strong id="selectedCollectionMethod">-</strong>
                    </div>

                    <div class="availability-actions">
                        <button type="button" id="confirmBookingBtn" class="btn btn-primary">
                            Confirm Booking
                        </button>
                    </div>

                </section>

                <!-- =========================================================
                     BOOKING SUCCESS

                     BACKEND: After successful save, populate:
                         #bookingReference - Generated reference (e.g. LB-00001)
                         #bookingStatus    - 'Pending'

                     The admin will later see this booking in Manage Bookings
                     and can Approve or Decline it.

                     Container ID: #bookingSuccess
                     Reveal after successful booking creation.
                ========================================================= -->
                <div id="bookingSuccess" class="booking-success hidden">
                    <h3><i class="fa-solid fa-circle-check"></i> Booking Successful</h3>
                    <p>Your booking has been submitted successfully.</p>
                    <p><strong>Booking Reference:</strong> <span id="bookingReference">Generated by PHP</span></p>
                    <p><strong>Status:</strong> <span id="bookingStatus">Pending</span></p>
                </div>

            </form>

        </div>
    </section>

    <script src="../JS/booking.js"></script>
</body>
</html>
