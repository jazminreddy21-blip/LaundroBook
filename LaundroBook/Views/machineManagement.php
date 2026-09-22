<?php
    if(session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    require_once __DIR__ . '/../Interfaces/Repositoryinterfaces.php';
    require_once __DIR__ . '/../Repositories/SystemManagerRepo.php';
    require_once __DIR__ . '/../Controllers/AdminController.php';

    $controller = new AdminController(new SystemManagerRepo());
    $controller->requireAuthentication();

    $statusFilter = trim($_GET['status'] ?? '');
    $machines = $controller->getMachines();

    if ($statusFilter !== '') {
        $machines = array_filter($machines, function ($machine) use ($statusFilter) {
            return $machine['machine_status'] === $statusFilter;
        });
    }
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>LaundroBook | Machine Management</title>

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

        <li><a href="machineManagement.php" class="active">Machines</a></li>

        <li><a href="customerManagement.php">Customers</a></li>

        <li><a href="reports.php">Reports</a></li>

        <li><a href="../Public/adminLogout.php">Logout</a></li>

    </ul>

</nav>

<main class="dashboard-container">

<section class="page-heading">

    <h2>Machine Management</h2>

    <p>

        View every washing machine and update its status.

    </p>

</section>

<?php if (isset($_GET['updated'])): ?>
    <div class="page-banner success">Machine status updated.</div>
<?php elseif (isset($_GET['error'])): ?>
    <div class="page-banner error">That status update was not valid.</div>
<?php endif; ?>

<section class="filter-section">

    <form id="machineFilterForm" name="machineFilterForm" method="GET">

        <div class="filter-group">

            <label for="status">Machine Status</label>

            <select id="status" name="status">

                <option value="">All</option>
                <?php foreach (['available', 'in_use', 'under_maintenance'] as $status): ?>
                    <option value="<?= $status; ?>" <?= $statusFilter === $status ? 'selected' : ''; ?>>
                        <?= ucwords(str_replace('_', ' ', $status)); ?>
                    </option>
                <?php endforeach; ?>

            </select>

        </div>

        <button type="submit" id="searchBtn" name="searchBtn">Filter</button>

    </form>

</section>

<section class="table-section">

    <table class="admin-table">

        <thead>

            <tr>

                <th>Machine ID</th>
                <th>Machine Name</th>
                <th>Status</th>
                <th>Action</th>

            </tr>

        </thead>

        <tbody id="machineTableBody" name="machineTableBody">

            <?php if (empty($machines)): ?>
                <tr class="empty-row"><td colspan="4">No machines match this filter.</td></tr>
            <?php else: ?>
                <?php foreach ($machines as $machine): ?>
                    <tr>
                        <td>#<?= (int)$machine['machine_id']; ?></td>
                        <td><?= htmlspecialchars($machine['machine_name']); ?></td>
                        <td>
                            <span class="status-pill status-<?= $machine['machine_status']; ?>">
                                <?= htmlspecialchars(str_replace('_', ' ', $machine['machine_status'])); ?>
                            </span>
                        </td>
                        <td>
                            <form class="action-form" method="POST" action="../Public/adminAction.php">
                                <input type="hidden" name="action" value="update_machine_status">
                                <input type="hidden" name="machine_id" value="<?= (int)$machine['machine_id']; ?>">
                                <select name="status">
                                    <?php foreach (['available', 'in_use', 'under_maintenance'] as $status): ?>
                                        <option value="<?= $status; ?>" <?= $machine['machine_status'] === $status ? 'selected' : ''; ?>>
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
