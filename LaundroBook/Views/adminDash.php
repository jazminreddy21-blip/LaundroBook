<?php
    if(session_status() == PHP_SESSION_NONE) {
        session_start();
    }
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>LaundroBook Admin Login</title>

    <!-- Font Awesome -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

    <!-- Admin CSS -->
    <link rel="stylesheet" href="../CSS/secondStyle.css">

</head>

<body>
<!------------------------------------------------------------
                        HEADER
------------------------------------------------------------->

<header class="admin-header">

    <div class="logo">

        <h1>LaundroBook Admin</h1>

    </div>

    <!-- =======================================================
         BACKEND INTEGRATION

         Data Source:
         Admin Table

         Purpose:
         Display the name of the currently logged-in administrator.

         Expected Output:
         Administrator Name

         Example:
         Welcome, Jazmin

         Insert the value inside the element below.

    ======================================================== -->

    <div class="admin-user">

        <p id="adminName" name="adminName"></p>

    </div>

</header>

<!------------------------------------------------------------
                    NAVIGATION BAR
------------------------------------------------------------->

<nav class="admin-navbar">

    <ul>

        <li>
            <a href="adminDash.php" class="active">
                Dashboard
            </a>
        </li>

        <li>
            <a href="bookingManagement.html">
                Bookings
            </a>
        </li>

        <li>
            <a href="pickupManagement.html">
                Pickups
            </a>
        </li>

        <li>
            <a href="deliveryManagement.html">
                Deliveries
            </a>
        </li>

        <li>
            <a href="machineManagement.html">
                Machines
            </a>
        </li>

        <li>
            <a href="customerManagement.html">
                Customers
            </a>
        </li>

        <li>
            <a href="reports.html">
                Reports
            </a>
        </li>

        <li>
            <a href="../Public/adminLogout.php">
                Logout
            </a>
        </li>

    </ul>

</nav>

<!------------------------------------------------------------
                    MAIN CONTENT
------------------------------------------------------------->

<main class="dashboard-container">

<!------------------------------------------------------------
                    WELCOME SECTION
------------------------------------------------------------->

<section class="welcome-section">

    <div class="welcome-text">

        <h2>

            Welcome Back!

        </h2>

        <p>

            Manage bookings, pickups, deliveries,
            washing machines and customers from one dashboard.

        </p>

    </div>

    <!-- =======================================================
         BACKEND INTEGRATION

         Data Source:
         System Date

         Purpose:
         Display today's date.

         Expected Output:
         Saturday, 1 August 2026

         Display the value inside the element below.

    ======================================================== -->

    <div class="welcome-date">

        <p>

            <strong>Today's Date:</strong>

            <span id="currentDate"
                  name="currentDate">

            </span>

        </p>

    </div>

</section>

<!------------------------------------------------------------
            DASHBOARD STATISTICS START HERE
------------------------------------------------------------->
<section class="dashboard-stats">

    <!-- ======================================================
         TODAY'S BOOKINGS

         BACKEND INTEGRATION

         Data Source:
         Bookings Table

         Purpose:
         Count the total number of bookings
         created for the current day.

         Display the result inside the element below.

    ======================================================= -->

    <div class="stat-card">

        <h3>Today's Bookings</h3>

        <h2 id="todayBookings"
            name="todayBookings"></h2>

    </div>

    <!-- ======================================================
         PENDING BOOKINGS

         BACKEND INTEGRATION

         Data Source:
         Bookings Table

         Purpose:
         Count all bookings with a status of
         'Pending'.

         Display the result inside the element below.

    ======================================================= -->

    <div class="stat-card">

        <h3>Pending Bookings</h3>

        <h2 id="pendingBookings"
            name="pendingBookings"></h2>

    </div>

    <!-- ======================================================
         TODAY'S PICKUPS

         BACKEND INTEGRATION

         Data Source:
         Pickups Table

         Purpose:
         Count all pickup requests scheduled
         for today.

         Display the result inside the element below.

    ======================================================= -->

    <div class="stat-card">

        <h3>Today's Pickups</h3>

        <h2 id="todayPickups"
            name="todayPickups"></h2>

    </div>

    <!-- ======================================================
         TODAY'S DELIVERIES

         BACKEND INTEGRATION

         Data Source:
         Deliveries Table

         Purpose:
         Count all deliveries scheduled
         for today.

         Display the result inside the element below.

    ======================================================= -->

    <div class="stat-card">

        <h3>Today's Deliveries</h3>

        <h2 id="todayDeliveries"
            name="todayDeliveries"></h2>

    </div>

    <!-- ======================================================
         AVAILABLE MACHINES

         BACKEND INTEGRATION

         Data Source:
         Machines Table

         Purpose:
         Count all machines currently available
         for customer bookings.

         Display the result inside the element below.

    ======================================================= -->

    <div class="stat-card">

        <h3>Available Machines</h3>

        <h2 id="availableMachines"
            name="availableMachines"></h2>

    </div>

    <!-- ======================================================
         TODAY'S REVENUE

         BACKEND INTEGRATION

         Data Source:
         Payments Table

         Purpose:
         Calculate the total revenue received
         for the current day.

         Format:
         South African Rand (R)

         Display the result inside the element below.

    ======================================================= -->

    <div class="stat-card">

        <h3>Today's Revenue</h3>

        <h2 id="todayRevenue"
            name="todayRevenue"></h2>

    </div>

</section>
<!------------------------------------------------------------
                SYSTEM NOTIFICATIONS
------------------------------------------------------------->

<section class="notifications-section">

    <div class="section-heading">

        <h2>System Notifications</h2>

        <p>
            Notifications requiring administrator attention.
        </p>

    </div>

    <!-- ======================================================
         BACKEND INTEGRATION

         Data Source:
         Bookings Table
         Machines Table
         Pickups Table
         Deliveries Table

         Purpose:
         Display notifications that require
         administrator attention.

         Populate:
         #notificationContainer

         The backend should generate one
         notification card for each notification.

         Suggested Notification Types:

         - Pending booking approvals
         - Machines requiring maintenance
         - Pickups awaiting driver assignment
         - Deliveries requiring attention

    ======================================================= -->

    <div id="notificationContainer"
         name="notificationContainer">

    </div>

    <!-- ======================================================
         BACKEND INTEGRATION

         Purpose:
         If there are no notifications to display,
         populate the element below with a suitable
         message such as:

         "There are currently no notifications."

         Hide this message whenever notifications
         are displayed.

         Populate:
         #noNotificationsMessage

    ======================================================= -->

    <p id="noNotificationsMessage"
       name="noNotificationsMessage">

    </p>

</section>

<!------------------------------------------------------------
                        FOOTER
------------------------------------------------------------->

<footer class="admin-footer">

    <p>

        &copy; 2026 LaundroBook.
        All Rights Reserved.

    </p>

</footer>

</main>

</body>

</html>