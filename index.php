<?php
require_once __DIR__ . '/config/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    switch ($_SESSION['role']) {
        case ROLE_ADMIN:
            redirect('views/admin/dashboard.php');
            break;
        case ROLE_INSTRUCTOR:
            redirect('views/instructor/dashboard.php');
            break;
        case ROLE_STUDENT:
            redirect('views/student/dashboard.php');
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <h1 class="auth-title">Welcome Back!</h1>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?php 
                    echo $_SESSION['success']; 
                    unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['reset_success'])): ?>
                <div class="alert alert-success">
                    Password reset successful! You can now login with your new password.
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <?php 
                    echo $_SESSION['error']; 
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <form action="controllers/AuthController.php" method="POST">
                <input type="hidden" name="action" value="login">
                
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" required placeholder="Enter your email">
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required placeholder="Enter your password">
                </div>

                <div style="text-align: right; margin-bottom: 1rem;">
                    <a href="views/auth/forgot_password.php" style="color: var(--primary-color); font-size: 0.9rem;">Forgot Password?</a>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
            </form>

            <p class="text-center mt-3">
                Don't have an account? <a href="views/auth/register.php" style="color: var(--primary-color); font-weight: 600;">Register here</a>
            </p>

            <div class="mt-3" style="padding: 1rem; background-color: var(--light-color); border-radius: 0.5rem;">
                <p style="font-size: 0.875rem; color: var(--text-secondary); margin-bottom: 0.5rem;">
                    <strong>Demo Accounts:</strong>
                </p>
                <p style="font-size: 0.75rem; color: var(--text-secondary); margin: 0;">
                    Admin: admin@lms.com / admin123
                </p>
            </div>
        </div>
    </div>
</body>
</html>
