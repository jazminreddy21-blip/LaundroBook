<?php
    require_once __DIR__. "/../controllers/BookingController.php"; 
    require_once __DIR__ . '/../repositories/SlotRepository.php';
    require_once __DIR__ . '/../repositories/MachineRepository.php';

    //then we can create an instance of a booking controller here
    //and then use the method that lists all the available machines
    //before using html to display everything in this page
    $slotRepository = new SlotRepository(); // real class, hardcoded body for now
    $machineRepository = new MachineRepository(); 

    $bookingController = new BookingController($slotRepository, $machineRepository);

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

            <!-- ==================================================
                 BOOKING FORM

                 PHP: Change action="#" to your PHP processing file.
                 Example: action="process-booking.php"

                 BACKEND BOOKING PROCESS:
                 1. Validate all booking information.
                 2. Use wash_type + load_type to query Service table.
                 3. Determine price, duration_minutes, duration_slots.
                 4. Check Machine table for available machines on date.
                 5. Check Slot table for available slots on date/machine.
                 6. For Heavy Wash (duration_slots = 2), ensure two
                    consecutive slots are free on the same machine.
                 7. Return available machines and slots to front end.
                 8. On Confirm: create booking with status = 'Pending'.
                 9. Generate Booking Reference Number.
                 10. Send booking confirmation email.
            ================================================== -->
            <form action="/LaundroBook/LaundroBook/LaundroBook/controllers/BookingController.php" method="POST" class="booking-form" id="bookingForm" novalidate>

                <!-- ==================================================
                     CLIENT SIDE VALIDATION
                     JavaScript validation messages appear here.
                     PHP should also validate all fields server-side.
                     Element ID: validationMessage
                ================================================== -->
                <div id="validationMessage" class="validation-message"></div>

                <!-- =========================================================
                     CUSTOMER INFORMATION SECTION

                     BACKEND INTEGRATION:
                     Purpose: Identify the customer making the booking.

                     Processing:
                     1. Validate all customer information.
                     2. Search Customer table using email address.
                     3. If email exists: retrieve existing customer_id.
                     4. If email does not exist: create new customer record.
                     5. Use customer_id when creating booking record.

                     Database Table: Customer
                     Fields:
                         customer_name   (VARCHAR)
                         customer_email  (VARCHAR, UNIQUE)
                         customer_phone  (VARCHAR)
                         address         (TEXT)

                     Form Field IDs:
                         #customer_name
                         #customer_email
                         #customer_phone
                ========================================================= -->
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

                <!-- =========================================================
                     BOOKING INFORMATION SECTION

                     BACKEND INTEGRATION:
                     Purpose: Capture date, service type, and collection preference.

                     NOTE: Machine and Time Slot are NOT selected here.
                     They are populated by the backend AFTER "Book Now"
                     checks availability.

                     Processing:
                     1. Validate booking_date is not in the past.
                     2. Use wash_type + load_type to query Service table.
                     3. Determine: price, duration_minutes, duration_slots.
                     4. Validate collection_method.
                     5. If 'delivery', validate delivery_address is provided.

                     Database Tables: Service

                     Form Field IDs:
                         #booking_date
                         #wash_type
                         #load_type
                         #collection_method
                         #address (delivery address)
                ========================================================= -->
                <section class="booking-details">

                    <h3>Booking Information</h3>
                    <p>Select your preferred date, service, and collection method.</p>

                    <!-- PHP: Required. Must not be in the past. Format: YYYY-MM-DD.
                         Backend uses this date to check Booking table for conflicts.
                         Database Field: booking_date (DATE) -->
                    <label for="booking_date">Booking Date</label>
                    <input type="date" name="booking_date" id="booking_date" required>

                    <!-- BACKEND: wash_type + load_type query the Service table.
                         Returns: price, duration_minutes, duration_slots.

                         duration_slots determines how many 45-min slots are needed:
                             Quick  = 1 slot  (25 min)
                             Normal = 1 slot  (35 min)
                             Heavy  = 2 slots (65 min)

                         Database Table: Service
                         Form Name: wash_type -->
                    <label for="wash_type">Wash Type</label>
                    <select name="wash_type" id="wash_type" required>
                        <option value="">Select Wash Type</option>
                        <option value="quick">Quick (25 Min)</option>
                        <option value="normal">Normal (35 Min)</option>
                        <option value="heavy">Heavy (65 Min)</option>
                    </select>

                    <!-- BACKEND: Combined with wash_type to find Service record.
                         Database Table: Service
                         Form Name: load_type -->
                    <label for="load_type">Load Type</label>
                    <select name="load_type" id="load_type" required>
                        <option value="">Select Load Type</option>
                        <option value="clothes">Clothes</option>
                        <option value="bedding">Bedding</option>
                        <option value="towels">Towels</option>
                    </select>

                    <!-- PHP: Required. Values: 'pickup' or 'delivery'.
                         If 'delivery', delivery_address becomes required.
                         Database Field: collection_method (ENUM or VARCHAR) -->
                    <label for="collection_method">Collection Method</label>
                    <select name="collection_method" id="collection_method" required>
                        <option value="">Select Collection Method</option>
                        <option value="pickup">Self Pickup</option>
                        <option value="delivery">Home Delivery</option>
                    </select>

                    <!-- PHP: Only validate when collection_method = 'delivery'.
                         Store with the booking record.
                         Form Name: delivery_address
                         Database Field: delivery_address (TEXT)
                         Container ID: #addressSection (toggled by JS) -->
                    <div id="addressSection" class="hidden">
                        <label for="address">Delivery Address</label>
                        <textarea name="delivery_address" id="address"
                            placeholder="Enter delivery address"></textarea>
                    </div>

                </section>

                <!-- Book Now Button -->
                <!--
                     Purpose: Triggers client-side validation FIRST.
                     If validation passes:
                         1. JS populates the service summary (price, duration).
                         2. The availability section is revealed.
                         3. Backend is called to check available machines/slots.

                     IMPORTANT: This does NOT create the booking.
                     The booking is only created on "Confirm Booking".

                     Button ID: #booking_button
                -->
                <div class="booking-actions">
                    <button type="button" id="booking_button" class="btn btn-primary">
                        Book Now
                    </button>
                </div>

                <!-- =========================================================
                     AVAILABILITY SECTION

                     This section is HIDDEN by default.
                     It is revealed AFTER "Book Now" passes validation.

                     BACKEND INTEGRATION:
                     After front-end validation:
                     1. Send wash_type, load_type, booking_date to backend.
                     2. Backend queries Service table for price/duration/slots.
                     3. Backend queries Machine table for available machines.
                     4. Backend queries Slot table for available time slots.
                     5. Backend checks Booking table for conflicts:
                            WHERE machine_id = X
                            AND slot_id = Y
                            AND booking_date = Z
                            AND status != 'cancelled'
                     6. For Heavy Wash (duration_slots = 2):
                            The selected slot AND the next consecutive slot
                            must BOTH be available on the same machine.
                     7. If conflict exists, suggest:
                            - Same slot on another machine, OR
                            - Next available slot.
                     8. Return available machines and slots to populate selects.

                     Container ID: #availabilitySection
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
                    <!-- JS populates from #booking_date -->
                    <div class="booking-date-result">
                        <span>Booking Date</span>
                        <strong id="selectedBookingDate">-</strong>
                    </div>

                    <!-- Machine and Slot Selection -->
                    <!--
                         BACKEND populates BOTH selects after availability check.

                         Machine: Only machines where machine_status = 'available'
                         and not already booked for the selected date/slot.

                         Slots: Only active slots (is_active = 1) that are not
                         already occupied. For Heavy Wash, two consecutive
                         slots must be free.

                         Available slots are based on:
                             08:00 - 08:45
                             08:45 - 09:30
                             09:30 - 10:15

                         Example (Heavy Wash on Machine 1):
                             08:00 - 08:45  ← Heavy Wash occupies this
                             08:45 - 09:30  ← Heavy Wash continues here
                             09:30 - 10:15  ← next available slot
                    -->
                    <div class="availability-grid">

                        <!-- BACKEND: Populate from Machine table.
                             Field ID: #machineSelect | Form Name: machine_id -->
                     <!--initially, I'll hardcode the available slots because we already know which slots
                     available for the test. But this has to be populated by JS dynamically because 
                     the slots are not fixed and so are the machines-->
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

                        <!-- BACKEND: Populate from Slot table.
                             Field ID: #slotSelect | Form Name: slot_id -->
                        <div class="form-group">
                            <label for="slotSelect">Available Time Slot</label>
                            <select id="slotSelect" name="slot_id">
                                <option value="">Select an available time slot</option>
                                <?php foreach ($availableSlots as $slot): ?>
                                <option value="<?php echo htmlspecialchars($slot); ?>">
                                <?php echo htmlspecialchars($slot); ?>
                                </option>
                                <?php endforeach; ?>
                                <!-- Backend populates available slots here -->
                            </select>
                        </div>

                    </div>

                    <!-- Collection Method Display -->
                    <!-- JS populates from #collection_method -->
                    <div class="booking-date-result">
                        <span>Collection Method</span>
                        <strong id="selectedCollectionMethod">-</strong>
                    </div>

                    <!-- Confirm Booking Button -->
                    <!--
                         BACKEND INTEGRATION:
                         When Confirm Booking (#confirmBookingBtn) is clicked:

                         1. Perform a FINAL availability check (prevent race conditions).
                         2. Save/retrieve customer from Customer table.
                         3. Create booking in Booking table with:
                                customer_id
                                machine_id
                                slot_id (for Heavy: slot_id AND slot_id + 1)
                                booking_date
                                wash_type
                                load_type
                                collection_method
                                delivery_address (if applicable)
                                total_price (from Service table)
                                status = 'Pending'
                         4. Generate booking reference (e.g. LB-00001).
                         5. Send confirmation email to customer_email.

                         NOTE: Slot.end_time determines when the machine
                         becomes available again. No free_at or machine_free_at needed.

                         Button ID: #confirmBookingBtn
                    -->
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
