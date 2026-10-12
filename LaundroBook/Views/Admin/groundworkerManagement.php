<?php
    if(session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    require_once __DIR__ . '/../../Interfaces/Repositoryinterfaces.php';
    require_once __DIR__ . '/../../Repositories/SystemManagerRepo.php';
    require_once __DIR__ . '/../../Controllers/AdminController.php';

    $controller = new AdminController(new SystemManagerRepo());
    $controller->requireAuthentication();
    $controller->requireSuperAdmin();

    $errors = $_SESSION['groundworker_errors'] ?? [];
    unset($_SESSION['groundworker_errors']);

    $success = $_SESSION['groundworker_success'] ?? '';
    unset($_SESSION['groundworker_success']);

    $groundworkers = $controller->getGroundworkerList();
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>LaundroBook | Groundworkers</title>

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
        <li><a href="customerManagement.php">Customers</a></li>
        <li><a href="enquiryManagement.php">Enquiries</a></li>
        <li><a href="reports.php">Reports</a></li>
        <?php if ($_SESSION['is_super_admin'] ?? false): ?>
            <li><a href="adminRegister.php">Add Admin</a></li>
            <li><a href="groundworkerManagement.php" class="active">Groundworkers</a></li>
        <?php endif; ?>
        <li><a href="../../Public/adminLogout.php">Logout</a></li>
    </ul>
</nav>

<main class="dashboard-container">

<section class="page-heading">
    <h2>Manage Groundworkers</h2>
    <p>Add the drivers and collectors who handle pickups and deliveries, or remove ones who are no longer needed.</p>
</section>

<?php if ($success): ?>
    <div class="page-banner success"><?= htmlspecialchars($success); ?></div>
<?php elseif (!empty($errors)): ?>
    <div class="page-banner error">
        <ul style="margin:0;padding-left:20px;">
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<section class="filter-section" style="max-width:450px;margin:0 auto;">

    <form method="POST" action="../../Public/adminAction.php" style="flex-direction:column;align-items:stretch;">
        <input type="hidden" name="action" value="add_groundworker">

        <div class="filter-group">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" maxlength="30" required>
        </div>

        <div class="filter-group">
            <label for="phone">Phone Number</label>
            <input type="tel" id="phone" name="phone" placeholder="0821234567" maxlength="20" required>
        </div>

        <div class="filter-group">
            <label for="role">Role</label>
            <select id="role" name="role" required>
                <option value="">Select Role</option>
                <option value="driver">Driver</option>
                <option value="collector">Collector</option>
            </select>
        </div>

        <button type="submit" style="margin-top:10px;">Add Groundworker</button>
    </form>

</section>

<section class="table-section" style="max-width:700px;margin:25px auto 0;">

    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Phone</th>
                <th>Role</th>
                <th>Jobs</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($groundworkers as $gw): ?>
                <tr>
                    <td>#<?= (int)$gw['groundworker_id']; ?></td>
                    <td><?= htmlspecialchars($gw['groundworker_name']); ?></td>
                    <td><?= htmlspecialchars($gw['groundworker_phone']); ?></td>
                    <td><?= htmlspecialchars(ucfirst($gw['groundworker_role'])); ?></td>
                    <td><?= (int)$gw['job_count']; ?></td>
                    <td>
                        <?php if ((int)$gw['job_count'] === 0 && count($groundworkers) > 1): ?>
                            <form method="POST" action="../../Public/adminAction.php"
                                  onsubmit="return confirm('Permanently remove this groundworker? This cannot be undone.');">
                                <input type="hidden" name="action" value="remove_groundworker">
                                <input type="hidden" name="groundworker_id" value="<?= (int)$gw['groundworker_id']; ?>">
                                <button type="submit" class="action-btn danger">Remove</button>
                            </form>
                        <?php elseif ((int)$gw['job_count'] > 0): ?>
                            <span style="color:#94A3B8;font-size:12px;">Has job records</span>
                        <?php else: ?>
                            <span style="color:#94A3B8;font-size:12px;">Last groundworker</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</section>

</main>

<footer class="admin-footer">
    <p>&copy; 2026 LaundroBook. All Rights Reserved.</p>
</footer>

</body>

</html>