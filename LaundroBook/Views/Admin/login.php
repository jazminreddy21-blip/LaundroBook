<?php
/*
    This file is included via require from AdminController::showLogin(),
    which runs either on a plain GET request, or after a failed login
    attempt (in which case $error is already set before this file is
    included). Not opened directly.
*/
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>LaundroBook Admin Login</title>

    <!-- Font Awesome -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

    <link rel="stylesheet" href="../../CSS/secondStyle.css">

</head>

<body class="login-page">

    <div class="login-container">

        <div class="login-card">

            <div class="login-header">

                <!-- ADDED from new-style version. If Images/logo.png
                     doesn't exist yet in your project, remove this line
                     and keep just the <i> icon below instead. -->
                <img src="../../Images/logo.png" class="login-logo" alt="LaundroBook Logo">

                <h1>LaundroBook</h1>

                <p>Administrator Portal</p>

            </div>
            <!-- Posts to username, matching AdminController::login()
                 and SystemManagerRepo::findManager(), which look up by
                 manager_username - system_manager has no email column,
                 so an email-based form (as in the new-style version)
                 would never be able to match a real row. -->
            <form action="../../Public/adminLogin.php" method="POST">
                <div class="form-group">

                    <label>Username</label>
                    <input
                        type="text"
                        id = "username"
                        name="username"
                        placeholder="Enter your username"
                        required>

                </div>

                <div class="form-group">

                    <label>Password</label>
                    <input
                        type="password"
                        id = "password"
                        name="password"
                        placeholder="Enter password"
                        required>

                </div>

                <?php if(!empty($error)) : ?>
                <div class="login-error visible">
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8')?>
                </div>
                <?php endif;?>
                <button type="submit" class="login-btn">

                    <i class="fa-solid fa-right-to-bracket"></i>

                    Login

                </button>

            </form>

        </div>

    </div>

</body>
<script src="../../JS/adminClientValidation.js"></script>

</html>