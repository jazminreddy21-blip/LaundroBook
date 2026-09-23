<?php
    if(session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    require_once __DIR__ . '/../../Interfaces/Repositoryinterfaces.php';
    require_once __DIR__ . '/../../Repositories/SystemManagerRepo.php';
    require_once __DIR__ . '/../../Controllers/AdminController.php';

    $controller = new AdminController(new SystemManagerRepo());
    $controller->requireAuthentication();

    $filters = [
        'search' => trim($_GET['bookingSearch'] ?? ''),
        'status' => trim($_GET['bookingStatus'] ?? ''),
        'date' => trim($_GET['bookingDate'] ?? ''),
    ];

    $bookings = $controller->getBookings($filters);

    // Simple counts derived from the already-loaded $bookings list, for
    // the summary cards below - no new queries needed, since this is
    // the same real data the table underneath already shows.
    $totalBookings = count($bookings);
    $readyCount = count(array_filter($bookings, fn($b) => strtolower($b['status']) === 'completed'));
    $pendingCount = count(array_filter($bookings, fn($b) => strtolower($b['status']) === 'pending'));
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>LaundroBook | Booking Management</title>

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
        <li><a href="bookingManagement.php" class="active">Bookings</a></li>
        <li><a href="pickupManagement.php">Pickups</a></li>
        <li><a href="deliveryManagement.php">Deliveries</a></li>
        <li><a href="machineManagement.php">Machines</a></li>
        <li><a href="customerManagement.php">Customers</a></li>
        <li><a href="reports.php">Reports</a></li>
        <li><a href="../../Public/adminLogout.php">Logout</a></li>
    </ul>
</nav>

<main class="dashboard-container">

<section class="page-heading">
    <h2>Booking Management</h2>
    <p>View, search and manage all customer bookings.</p>
</section>

<?php if (isset($_GET['updated'])): ?>
    <div class="page-banner success">Booking status updated.</div>
<?php elseif (isset($_GET['error'])): ?>
    <div class="page-banner error">
        <?= $_GET['error'] === 'not_found' ? 'That booking could not be found.' : 'That status update was not valid.'; ?>
    </div>
<?php endif; ?>

<!-- ADDED from new-style version: summary cards, computed from the
     real $bookings data already loaded above - no fake/dummy values. -->
<section class="booking-summary">
    <div class="booking-stat-card">
        <div class="booking-stat-icon"><i class="fa-solid fa-calendar-check"></i></div>
        <div class="booking-stat-info">
            <h3>Total Bookings</h3>
            <h2><?= $totalBookings; ?></h2>
        </div>
    </div>
    <div class="booking-stat-card">
        <div class="booking-stat-icon"><i class="fa-solid fa-clock"></i></div>
        <div class="booking-stat-info">
            <h3>Pending</h3>
            <h2><?= $pendingCount; ?></h2>
        </div>
    </div>
    <div class="booking-stat-card">
        <div class="booking-stat-icon"><i class="fa-solid fa-bag-shopping"></i></div>
        <div class="booking-stat-info">
            <h3>Completed</h3>
            <h2><?= $readyCount; ?></h2>
        </div>
    </div>
</section>

<section class="filter-section">

    <form id="bookingSearchForm" name="bookingSearchForm" method="GET">

        <div class="filter-group">
            <label for="bookingSearch">Booking Reference / Customer Name</label>
            <input type="text" id="bookingSearch" name="bookingSearch"
                   value="<?= htmlspecialchars($filters['search']); ?>">
        </div>

        <div class="filter-group">
            <label for="bookingStatus">Booking Status</label>
            <select id="bookingStatus" name="bookingStatus">
                <option value="">All</option>
                <?php // FIXED: status list now matches booking.status's
                      // real CHECK constraint (pending, in_progress,
                      // completed, cancelled) - the new-style version
                      // had a longer list (accepted, in_washing,
                      // ready_for_collection, etc.) that doesn't exist
                      // in the database and would fail to save. ?>
                <?php foreach (['pending', 'in_progress', 'completed', 'cancelled'] as $status): ?>
                    <option value="<?= $status; ?>" <?= $filters['status'] === $status ? 'selected' : ''; ?>>
                        <?= ucwords(str_replace('_', ' ', $status)); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="filter-group">
            <label for="bookingDate">Booking Date</label>
            <input type="date" id="bookingDate" name="bookingDate"
                   value="<?= htmlspecialchars($filters['date']); ?>">
        </div>

        <button type="submit" id="searchBtn" name="searchBtn">Search</button>

    </form>

</section>

<section class="table-section">

    <table class="admin-table">
        <thead>
            <tr>
               <th>Booking Reference</th>
               <th>Customer</th>
               <th>Booking Date</th>
               <th>Time Slot</th>
               <th>Machine</th>
               <th>Service</th>
               <th>Price</th>
               <th>Status</th>
               <th>Action</th>
            </tr>
        </thead>
        <tbody id="bookingTableBody" name="bookingTableBody">
            <?php if (empty($bookings)): ?>
                <tr class="empty-row"><td colspan="9">No bookings match these filters.</td></tr>
            <?php else: ?>
                <?php foreach ($bookings as $booking): ?>
                    <tr>
                        <td><?= htmlspecialchars($booking['booking_reference']); ?></td>
                        <td><?= htmlspecialchars($booking['customer_name']); ?></td>
                        <td><?= htmlspecialchars(date('d M Y', strtotime($booking['booking_date']))); ?></td>
                        <td><?= htmlspecialchars($booking['slot_label']); ?></td>
                        <td><?= htmlspecialchars($booking['machine_name']); ?></td>
                        <td><?= htmlspecialchars(ucfirst($booking['wash_type']) . ' / ' . ucfirst($booking['load_type'])); ?></td>
                        <td>R<?= number_format((float)$booking['total_price'], 2); ?></td>
                        <td>
                            <span class="status-pill status-<?= strtolower($booking['status']); ?>">
                                <?= htmlspecialchars(str_replace('_', ' ', $booking['status'])); ?>
                            </span>
                        </td>
                        <td>
                            <form class="action-form" method="POST" action="../../Public/adminAction.php">
                                <input type="hidden" name="action" value="update_booking_status">
                                <input type="hidden" name="booking_reference" value="<?= htmlspecialchars($booking['booking_reference']); ?>">
                                <select name="status">
                                    <?php foreach (['pending', 'in_progress', 'completed', 'cancelled'] as $status): ?>
                                        <option value="<?= $status; ?>" <?= strtolower($booking['status']) === $status ? 'selected' : ''; ?>>
                                            <?= ucwords(str_replace('_', ' ', $status)); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="action-btn">Update</button>
                            </form>
                        </td>
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