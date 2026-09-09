<?php
require_once __DIR__ . '/../Services/EmailService.php';

$sent = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['test_email'])) {
    $config = require __DIR__ . '/../Config/EmailConfig.php';
    $emailService = new EmailService($config);

    $sent = $emailService->sendBookingConfirmation(
        $_POST['test_email'],
        'LB-00001',
        ['wash_type' => 'quick', 'load_type' => 'clothes', 'price' => 30.00, 'duration_minutes' => 25],
        date('Y-m-d'),
        'Machine 1',
        '08:00 - 08:45',
        null
    );
}
?>
<form method="POST">
    <input type="email" name="test_email" placeholder="Enter an email to test" required>
    <button type="submit">Send Test Email</button>
</form>

<?php if ($sent !== null): ?>
    <p><?= $sent ? "Email sent successfully!" : "Email failed to send." ?></p>
<?php endif; ?>