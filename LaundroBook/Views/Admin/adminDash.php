<?php
    if(session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    require_once __DIR__ . '/../../Interfaces/Repositoryinterfaces.php';
    require_once __DIR__ . '/../../Repositories/SystemManagerRepo.php';
    require_once __DIR__ . '/../../Controllers/AdminController.php';

    $controller = new AdminController(new SystemManagerRepo());

    //block access if it's directly from url without logging in
    $controller->requireAuthentication();

    $dashboard = $controller->getDashboardData();
    $stats = $dashboard['stats'];
    $notifications = $dashboard['notifications'];
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- FIXED: was "LaundroBook Admin Login", a leftover copy-paste
         from login.php - this page is the dashboard, not the login
         screen. -->
    <title>LaundroBook | Admin Dashboard</title>

    <!-- Font Awesome -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

    <!-- Admin CSS -->
    <link rel="stylesheet" href="../../CSS/secondStyle.css">

</head>

<body>
<!------------------------------------------------------------
                        HEADER
------------------------------------------------------------->

<header class="admin-header">

    <div class="logo">

        <!-- ADDED from new-style version. If Images/logo.png doesn't
             actually exist in your project yet, this shows a broken
             image icon - either add the file, or remove this <img>
             line and keep just the <h1> as before. -->
        <img src="../../Images/logo.png" class="admin-logo" alt="LaundroBook Logo">
        <h1>LaundroBook Admin</h1>

    </div>

    <div class="admin-user">

        <p id="adminName" name="adminName"><?= htmlspecialchars($_SESSION['username']?? 'Admin');?></p>
    </div>

</header>

<nav class="admin-navbar">

    <ul>

        <li>
            <a href="adminDash.php" class="active">
                Dashboard
            </a>
        </li>

        <li>
            <a href="bookingManagement.php">
                Bookings
            </a>
        </li>

        <li>
            <a href="pickupManagement.php">
                Pickups
            </a>
        </li>

        <li>
            <a href="deliveryManagement.php">
                Deliveries
            </a>
        </li>

        <li>
            <a href="machineManagement.php">
                Machines
            </a>
        </li>

        <li>
            <a href="customerManagement.php">
                Customers
            </a>
        </li>

        <li>
            <a href="enquiryManagement.php">
                Enquiries
            </a>
        </li>

        <li>
            <a href="reports.php">
                Reports
            </a>
        </li>

        <li>
            <a href="../../Public/adminLogout.php">
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

    <div class="welcome-date">

        <p>

            <strong>Today's Date: <?=htmlspecialchars(date("F j, Y"));?></strong>
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

    <div class="stat-card">

        <h3>Today's Bookings</h3>

        <h2 id="todayBookings"
            name="todayBookings"><?= (int)$stats['today_bookings']; ?></h2>

    </div>

    <div class="stat-card">

        <h3>Pending Bookings</h3>

        <h2 id="pendingBookings"
            name="pendingBookings"><?= (int)$stats['pending_bookings']; ?></h2>

    </div>

    <div class="stat-card">

        <h3>Today's Pickups</h3>

        <h2 id="todayPickups"
            name="todayPickups"><?= (int)$stats['today_pickups']; ?></h2>

    </div>

    <div class="stat-card">

        <h3>Today's Deliveries</h3>

        <h2 id="todayDeliveries"
            name="todayDeliveries"><?= (int)$stats['today_deliveries']; ?></h2>

    </div>

    <div class="stat-card">

        <h3>Available Machines</h3>

        <h2 id="availableMachines"
            name="availableMachines"><?= (int)$stats['available_machines']; ?></h2>

    </div>

    <div class="stat-card">

        <h3>Today's Revenue</h3>

        <h2 id="todayRevenue"
            name="todayRevenue">R<?= number_format((float)$stats['today_revenue'], 2); ?></h2>

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

    <div id="notificationContainer"
         name="notificationContainer">

        <?php foreach ($notifications as $notification): ?>
            <a class="notification-card notification-<?= htmlspecialchars($notification['type']); ?>"
               href="<?= htmlspecialchars($notification['link']); ?>">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span><?= htmlspecialchars($notification['message']); ?></span>
            </a>
        <?php endforeach; ?>

    </div>

    <p id="noNotificationsMessage"
       name="noNotificationsMessage"
       <?= !empty($notifications) ? 'style="display:none;"' : ''; ?>>

        <?= empty($notifications) ? 'There are currently no notifications.' : ''; ?>

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