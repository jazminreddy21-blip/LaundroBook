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
        'search' => trim($_GET['deliverySearch'] ?? ''),
        'status' => trim($_GET['deliveryStatus'] ?? ''),
    ];

    // getDeliveries() forces delivery_type = 'delivery' internally, so
    // this page only ever shows drop-offs, never pickups.
    $deliveries = $controller->getDeliveries($filters);
    $groundworkers = $controller->getGroundworkers();

    $totalDeliveries = count($deliveries);
    $completedCount = count(array_filter($deliveries, fn($d) => strtolower($d['delivery_status']) === 'completed'));
    $pendingCount = count(array_filter($deliveries, fn($d) => strtolower($d['delivery_status']) === 'pending'));
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>LaundroBook | Delivery Management</title>

    <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

    <link rel="stylesheet" href="../../CSS/secondStyle.css">

</head>

<body>

<header class="admin-header">
    <div class="logo">
        <img src="../../Images/logo.png" class="admin-logo" alt="LaundroBook Logo">
        <h1>LaundroBook Admin Page</h1>
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
        <li><a href="deliveryManagement.php" class="active">Deliveries</a></li>
        <li><a href="machineManagement.php">Machines</a></li>
        <li><a href="customerManagement.php">Customers</a></li>
        <li><a href="enquiryManagement.php">Enquiries</a></li>
        <li><a href="reports.php">Reports</a></li>
        <?php if ($_SESSION['is_super_admin'] ?? false): ?>
            <li><a href="adminRegister.php">Add Admin</a></li>
        <?php endif; ?>
        <li><a href="../../Public/adminLogout.php">Logout</a></li>
    </ul>
</nav>

<main class="dashboard-container">

<section class="page-heading">
    <h2>Delivery Management</h2>
    <p>View and manage completed laundry drop-offs back to customers.</p>
</section>

<?php if (isset($_GET['updated'])): ?>
    <div class="page-banner success">Delivery status updated.</div>
<?php elseif (isset($_GET['error']) && $_GET['error'] === 'booking_not_started'): ?>
    <div class="page-banner error">This booking hasn't started yet - it must be marked "In Progress" on the Booking Management page before pickup or delivery can begin.</div>
<?php elseif (isset($_GET['error']) && $_GET['error'] === 'collection_not_complete'): ?>
    <div class="page-banner error">This delivery cannot be updated yet - the pickup for this booking must be marked "Completed" first.</div>
<?php elseif (isset($_GET['error'])): ?>
    <div class="page-banner error">That status update was not valid.</div>
<?php endif; ?>

<!-- ADDED from new-style version: summary cards, computed from the
     real $deliveries data already loaded above. -->
<section class="booking-summary">
    <div class="booking-stat-card">
        <div class="booking-stat-icon"><i class="fa-solid fa-truck"></i></div>
        <div class="booking-stat-info">
            <h3>Total Deliveries</h3>
            <h2><?= $totalDeliveries; ?></h2>
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
        <div class="booking-stat-icon"><i class="fa-solid fa-check"></i></div>
        <div class="booking-stat-info">
            <h3>Completed</h3>
            <h2><?= $completedCount; ?></h2>
        </div>
    </div>
</section>

<section class="filter-section">

    <form id="deliverySearchForm" name="deliverySearchForm" method="GET">
        <div class="filter-group">
            <label for="deliverySearch">Booking Reference / Customer Name</label>
            <input type="text" id="deliverySearch" name="deliverySearch"
                   value="<?= htmlspecialchars($filters['search']); ?>">
        </div>
        <div class="filter-group">
            <label for="deliveryStatus">Delivery Status</label>
            <select id="deliveryStatus" name="deliveryStatus">
                <option value="">All</option>
                <?php // Matches delivery.delivery_status's real CHECK
                      // constraint (pending, in_progress, completed). ?>
                <?php foreach (['pending', 'in_progress', 'completed'] as $status): ?>
                    <option value="<?= $status; ?>" <?= $filters['status'] === $status ? 'selected' : ''; ?>>
                        <?= ucwords(str_replace('_', ' ', $status)); ?>
                    </option>
                <?php endforeach; ?>
            </select>
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
                <th>Address</th>
                <th>Ground Worker</th>
                <th>Scheduled Time</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="deliveryTableBody" name="deliveryTableBody">
            <?php if (empty($deliveries)): ?>
                <tr class="empty-row"><td colspan="7">No deliveries match these filters.</td></tr>
            <?php else: ?>
                <?php foreach ($deliveries as $delivery): ?>
                    <tr>
                        <td><?= htmlspecialchars($delivery['booking_reference']); ?></td>
                        <td><?= htmlspecialchars($delivery['customer_name']); ?></td>
                        <td><?= htmlspecialchars($delivery['address'] ?? '—'); ?></td>
                        <td><?= htmlspecialchars($delivery['groundworker_name']); ?></td>
                        <td><?= htmlspecialchars(date('d M Y, H:i', strtotime($delivery['scheduled_time']))); ?></td>
                        <td>
                            <span class="status-pill status-<?= strtolower($delivery['delivery_status']); ?>">
                                <?= htmlspecialchars(str_replace('_', ' ', $delivery['delivery_status'])); ?>
                            </span>
                        </td>
                        <td>
                            <form class="action-form" method="POST" action="../../Public/adminAction.php">
                                <input type="hidden" name="action" value="update_delivery_status">
                                <input type="hidden" name="delivery_id" value="<?= (int)$delivery['delivery_id']; ?>">
                                <input type="hidden" name="delivery_type" value="delivery">
                                <select name="status">
                                    <?php foreach (['pending', 'in_progress', 'completed'] as $status): ?>
                                        <option value="<?= $status; ?>" <?= strtolower($delivery['delivery_status']) === $status ? 'selected' : ''; ?>>
                                            <?= ucwords(str_replace('_', ' ', $status)); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="action-btn">Update</button>
                            </form>

                            <!-- ADDED: separate form for reassigning which
                                 groundworker is handling this delivery -
                                 kept as its own form/action rather than
                                 combined with the status update above,
                                 since they're conceptually different
                                 changes, matching how every other admin
                                 action in this project is already scoped
                                 to one concern each. -->
                            <form class="action-form" method="POST" action="../../Public/adminAction.php" style="margin-top:6px;">
                                <input type="hidden" name="action" value="reassign_groundworker">
                                <input type="hidden" name="delivery_id" value="<?= (int)$delivery['delivery_id']; ?>">
                                <input type="hidden" name="delivery_type" value="delivery">
                                <select name="groundworker_id">
                                    <?php foreach ($groundworkers as $gw): ?>
                                        <option value="<?= (int)$gw['groundworker_id']; ?>" <?= (int)$delivery['groundworker_id'] === (int)$gw['groundworker_id'] ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($gw['groundworker_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="action-btn">Reassign</button>
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