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

    $errors = $_SESSION['admin_register_errors'] ?? [];
    unset($_SESSION['admin_register_errors']);

    $success = $_SESSION['admin_register_success'] ?? false;
    unset($_SESSION['admin_register_success']);

    $admins = $controller->getAdmins();
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>LaundroBook | Add Admin</title>

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
        <li><a href="deliveryManagement.php">Deliveries</a></li>
        <li><a href="machineManagement.php">Machines</a></li>
        <li><a href="customerManagement.php">Customers</a></li>
        <li><a href="enquiryManagement.php">Enquiries</a></li>
        <li><a href="reports.php">Reports</a></li>
        <?php if ($_SESSION['is_super_admin'] ?? false): ?>
            <li><a href="adminRegister.php" class="active">Add Admin</a></li>
        <?php endif; ?>
        <li><a href="../../Public/adminLogout.php">Logout</a></li>
    </ul>
</nav>

<main class="dashboard-container">

<section class="page-heading">
    <h2>Manage Admins</h2>
    <p>Only visible to the super admin. Create new admin accounts, or remove ones that are no longer needed.</p>
</section>

<?php if ($success): ?>
    <div class="page-banner success">Action completed successfully.</div>
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
        <input type="hidden" name="action" value="register_admin">

        <div class="filter-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>
        </div>

        <div class="filter-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>

        <div class="filter-group">
            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>

        <button type="submit" style="margin-top:10px;">Create Admin Account</button>
    </form>

</section>

<section class="table-section" style="max-width:600px;margin:25px auto 0;">

    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Role</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($admins as $admin): ?>
                <?php $isThisSuperAdmin = $controller->isSuperAdmin((int)$admin['manager_id']); ?>
                <tr>
                    <td>#<?= (int)$admin['manager_id']; ?></td>
                    <td><?= htmlspecialchars($admin['manager_username']); ?></td>
                    <td>
                        <?php if ($isThisSuperAdmin): ?>
                            <span class="status-pill status-completed">Super Admin</span>
                        <?php else: ?>
                            <span class="status-pill status-pending">Admin</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!$isThisSuperAdmin): ?>
                            <form method="POST" action="../../Public/adminAction.php"
                                  onsubmit="return confirm('Permanently remove this admin account? This cannot be undone.');">
                                <input type="hidden" name="action" value="remove_admin">
                                <input type="hidden" name="manager_id" value="<?= (int)$admin['manager_id']; ?>">
                                <button type="submit" class="action-btn danger">Remove</button>
                            </form>
                        <?php else: ?>
                            <span style="color:#94A3B8;font-size:12px;">Cannot be removed</span>
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