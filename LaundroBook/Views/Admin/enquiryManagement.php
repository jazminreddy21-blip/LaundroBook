<?php
    if(session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    require_once __DIR__ . '/../../Interfaces/Repositoryinterfaces.php';
    require_once __DIR__ . '/../../Repositories/SystemManagerRepo.php';
    require_once __DIR__ . '/../../Controllers/AdminController.php';

    $controller = new AdminController(new SystemManagerRepo());
    $controller->requireAuthentication();

    $search = trim($_GET['enquirySearch'] ?? '');
    $enquiries = $controller->getEnquiries($search);

    $totalEnquiries = count($enquiries);
    $pendingCount = count(array_filter($enquiries, fn($e) => $e['status'] === 'Pending'));
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>LaundroBook | Enquiry Management</title>

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
        <li><a href="enquiryManagement.php" class="active">Enquiries</a></li>
        <li><a href="reports.php">Reports</a></li>
        <li><a href="../../Public/adminLogout.php">Logout</a></li>
    </ul>
</nav>

<main class="dashboard-container">

<section class="page-heading">
    <h2>Enquiry Management</h2>
    <p>Messages submitted through the site's Contact page.</p>
</section>

<?php if (isset($_GET['updated'])): ?>
    <div class="page-banner success">Enquiry status updated.</div>
<?php elseif (isset($_GET['error'])): ?>
    <div class="page-banner error">That status update was not valid.</div>
<?php endif; ?>

<section class="customer-summary">
    <div class="customer-stat-card">
        <div class="customer-stat-icon"><i class="fa-solid fa-envelope"></i></div>
        <div class="customer-stat-info">
            <h3>Total Enquiries</h3>
            <h2><?= $totalEnquiries; ?></h2>
        </div>
    </div>
    <div class="customer-stat-card">
        <div class="customer-stat-icon"><i class="fa-solid fa-clock"></i></div>
        <div class="customer-stat-info">
            <h3>Pending</h3>
            <h2><?= $pendingCount; ?></h2>
        </div>
    </div>
</section>

<section class="filter-section">

    <form id="enquirySearchForm" name="enquirySearchForm" method="GET">
        <div class="filter-group">
            <label for="enquirySearch">Name / Email / Message</label>
            <input type="text" id="enquirySearch" name="enquirySearch"
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
                <th>Message</th>
                <th>Date Submitted</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="enquiryTableBody" name="enquiryTableBody">
            <?php if (empty($enquiries)): ?>
                <tr class="empty-row"><td colspan="6">No enquiries match this search.</td></tr>
            <?php else: ?>
                <?php foreach ($enquiries as $enquiry): ?>
                    <tr>
                        <td><?= htmlspecialchars($enquiry['name']); ?></td>
                        <td><?= htmlspecialchars($enquiry['email']); ?></td>
                        <td style="max-width:300px;white-space:pre-wrap;"><?= htmlspecialchars($enquiry['message']); ?></td>
                        <td><?= htmlspecialchars(date('d M Y, H:i', strtotime($enquiry['date_submitted']))); ?></td>
                        <td>
                            <span class="status-pill status-<?= strtolower($enquiry['status']); ?>">
                                <?= htmlspecialchars($enquiry['status']); ?>
                            </span>
                        </td>
                        <td>
                            <form class="action-form" method="POST" action="../../Public/adminAction.php">
                                <input type="hidden" name="action" value="update_enquiry_status">
                                <input type="hidden" name="enquiry_id" value="<?= (int)$enquiry['enquiry_id']; ?>">
                                <select name="status">
                                    <?php foreach (['Pending', 'Responded'] as $status): ?>
                                        <option value="<?= $status; ?>" <?= $enquiry['status'] === $status ? 'selected' : ''; ?>>
                                            <?= $status; ?>
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