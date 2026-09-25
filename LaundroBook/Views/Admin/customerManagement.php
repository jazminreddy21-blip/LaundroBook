<?php
    if(session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    require_once __DIR__ . '/../../Interfaces/Repositoryinterfaces.php';
    require_once __DIR__ . '/../../Repositories/SystemManagerRepo.php';
    require_once __DIR__ . '/../../Controllers/AdminController.php';

    $controller = new AdminController(new SystemManagerRepo());
    $controller->requireAuthentication();

    $search = trim($_GET['customerSearch'] ?? '');
    $customers = $controller->getCustomers($search);

    // Real counts from the already-loaded $customers list, matching
    // new-style's summary card layout without inventing fake numbers.
    $totalCustomers = count($customers);
    $totalBookingsAcrossCustomers = array_sum(array_column($customers, 'total_bookings'));
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>LaundroBook | Customer Management</title>

    <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

    <link rel="stylesheet" href="../../CSS/secondStyle.css">

</head>

<body>

<header class="admin-header">
    <div class="logo">
        <img src="../../Images/logo.png" class="admin-logo" alt="LaundroBook Logo">
        <h1>LaundroBook Admin</h1>
    </div>
    <div class="admin-user">
        <p id="adminName" name="adminName"><?= htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></p>
    </div>
</header>

<nav class="admin-navbar">
    <ul>
        <li><a href="adminDash.php">Dashboard</a></li>
        <li><a href="bookingManagement.php">Bookings</a></li>
        <li><a href="pickupManagement.php">Pickups</a></li>
        <li><a href="deliveryManagement.php">Deliveries</a></li>
        <li><a href="machineManagement.php">Machines</a></li>
        <li><a href="customerManagement.php" class="active">Customers</a></li>
        <li><a href="enquiryManagement.php">Enquiries</a></li>
        <li><a href="reports.php">Reports</a></li>
        <li><a href="../../Public/adminLogout.php">Logout</a></li>
    </ul>
</nav>

<main class="dashboard-container">

<section class="page-heading">
    <h2>Customer Management</h2>
    <p>Browse the customer directory and how many bookings each one has made.</p>
</section>

<!-- ADDED from new-style version: summary cards, computed from the
     real $customers data already loaded above. -->
<section class="customer-summary">
    <div class="customer-stat-card">
        <div class="customer-stat-icon"><i class="fa-solid fa-users"></i></div>
        <div class="customer-stat-info">
            <h3>Total Customers</h3>
            <h2><?= $totalCustomers; ?></h2>
        </div>
    </div>
    <div class="customer-stat-card">
        <div class="customer-stat-icon"><i class="fa-solid fa-calendar-check"></i></div>
        <div class="customer-stat-info">
            <h3>Total Bookings</h3>
            <h2><?= $totalBookingsAcrossCustomers; ?></h2>
        </div>
    </div>
</section>

<section class="filter-section">

    <form id="customerSearchForm" name="customerSearchForm" method="GET">
        <div class="filter-group">
            <label for="customerSearch">Name / Email / Phone</label>
            <input type="text" id="customerSearch" name="customerSearch"
                   value="<?= htmlspecialchars($search); ?>">
        </div>
        <button type="submit" id="searchBtn" name="searchBtn">Search</button>
    </form>

</section>

<section class="table-section">

    <table class="admin-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Address</th>
                <th>Total Bookings</th>
            </tr>
        </thead>
        <tbody id="customerTableBody" name="customerTableBody">
            <?php if (empty($customers)): ?>
                <tr class="empty-row"><td colspan="5">No customers match this search.</td></tr>
            <?php else: ?>
                <?php foreach ($customers as $customer): ?>
                    <tr>
                        <td><?= htmlspecialchars($customer['customer_name']); ?></td>
                        <td><?= htmlspecialchars($customer['customer_email']); ?></td>
                        <td><?= htmlspecialchars($customer['customer_phone']); ?></td>
                        <td><?= htmlspecialchars($customer['address'] ?? '—'); ?></td>
                        <td><?= (int)$customer['total_bookings']; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

</section>

</main>

<footer class="admin-footer">
    <p>&copy; 2026 LaundroBook. All Rights Reserved.</p>
</footer>

</body>

</html>