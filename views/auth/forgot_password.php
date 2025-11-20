<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - E-Learning Management System</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <h1>Reset Password</h1>
            <p style="color: var(--text-secondary); margin-bottom: 2rem;">
                Enter your email address and we'll send you a reset link.
            </p>
            
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    Password reset link generated! 
                    <a href="../../controllers/PasswordController.php?action=show_reset_link" style="color: #155724; font-weight: 600;">Click here to view the reset link</a>
                    (In production, this would be emailed to you)
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>

            <form action="../../controllers/PasswordController.php" method="POST">
                <input type="hidden" name="action" value="forgot_password">
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Send Reset Link</button>

                <div style="text-align: center; margin-top: 1rem;">
                    <a href="../../index.php" style="color: var(--primary-color);">Back to Login</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
