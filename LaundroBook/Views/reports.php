<?php
    if(session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    require_once __DIR__ . '/../Interfaces/Repositoryinterfaces.php';
    require_once __DIR__ . '/../Repositories/SystemManagerRepo.php';
    require_once __DIR__ . '/../Controllers/AdminController.php';

    $controller = new AdminController(new SystemManagerRepo());
    $controller->requireAuthentication();

    $report = $controller->getReportData();
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>LaundroBook | Reports</title>

    <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

    <link rel="stylesheet" href="../CSS/secondStyle.css">

</head>

<body>

<header class="admin-header">

    <div class="logo">

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

        <li><a href="customerManagement.php">Customers</a></li>

        <li><a href="reports.php" class="active">Reports</a></li>

        <li><a href="../Public/adminLogout.php">Logout</a></li>

    </ul>

</nav>

<main class="dashboard-container">

<section class="page-heading">

    <h2>Reports</h2>

    <p>

        A quick summary of revenue and booking activity over the last 7 and 30 days.

    </p>

</section>

<section class="report-grid">

    <div class="stat-card">
        <h3>Revenue (Last 7 Days)</h3>
        <h2>R<?= number_format($report['revenue_7_days'], 2); ?></h2>
    </div>

    <div class="stat-card">
        <h3>Revenue (Last 30 Days)</h3>
        <h2>R<?= number_format($report['revenue_30_days'], 2); ?></h2>
    </div>

    <div class="stat-card">
        <h3>Total Customers</h3>
        <h2><?= (int)$report['total_customers']; ?></h2>
    </div>

    <div class="stat-card">
        <h3>Completed Bookings (30 Days)</h3>
        <h2><?= (int)$report['completed_30_days']; ?></h2>
    </div>

    <div class="stat-card">
        <h3>Cancelled Bookings (30 Days)</h3>
        <h2><?= (int)$report['cancelled_30_days']; ?></h2>
    </div>

</section>

<section class="section-heading">
    <h2>Service Price List</h2>
    <p>Every wash/load combination currently on offer.</p>
</section>

<section class="table-section">

    <table class="admin-table">

        <thead>

            <tr>

                <th>Wash Type</th>
                <th>Load Type</th>
                <th>Price</th>
                <th>Duration</th>

            </tr>

        </thead>

        <tbody>

            <?php if (empty($report['services'])): ?>
                <tr class="empty-row"><td colspan="4">No services configured yet.</td></tr>
            <?php else: ?>
                <?php foreach ($report['services'] as $service): ?>
                    <tr>
                        <td><?= htmlspecialchars(ucfirst($service['wash_type'])); ?></td>
                        <td><?= htmlspecialchars(ucfirst($service['load_type'])); ?></td>
                        <td>R<?= number_format((float)$service['price'], 2); ?></td>
                        <td><?= (int)$service['duration_minutes']; ?> min</td>
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
