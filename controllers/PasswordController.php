<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Create database connection
$db = new Database();
$conn = $db->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'forgot_password') {
        $email = sanitizeInput($_POST['email']);

        // Check if user exists
        $stmt = $conn->prepare("SELECT user_id, full_name FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user) {
            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Save token to database
            $stmt = $conn->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $user['user_id'], $token, $expires_at);
            $stmt->execute();

            // Create reset link
            $reset_link = BASE_URL . "views/auth/reset_password.php?token=" . $token;

            // In production, send email here
            // For demo purposes, we'll display the link
            error_log("Password Reset Link for {$email}: {$reset_link}");
            
            // Store link in session for demo purposes
            session_start();
            $_SESSION['reset_link'] = $reset_link;
            $_SESSION['reset_email'] = $email;
        }

        // Always show success to prevent email enumeration
        redirect('views/auth/forgot_password.php?success=1');
    }

    if ($action === 'reset_password') {
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Validate passwords match
        if ($password !== $confirm_password) {
            redirect('views/auth/reset_password.php?token=' . urlencode($token) . '&error=' . urlencode('Passwords do not match'));
        }

        // Validate password length
        if (strlen($password) < 6) {
            redirect('views/auth/reset_password.php?token=' . urlencode($token) . '&error=' . urlencode('Password must be at least 6 characters'));
        }

        // Verify token
        $stmt = $conn->prepare("SELECT pr.reset_id, pr.user_id, pr.expires_at, pr.used 
                               FROM password_resets pr 
                               WHERE pr.token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        $reset = $result->fetch_assoc();

        if (!$reset) {
            redirect('views/auth/reset_password.php?token=' . urlencode($token) . '&error=' . urlencode('Invalid reset token'));
        }

        if ($reset['used']) {
            redirect('views/auth/reset_password.php?token=' . urlencode($token) . '&error=' . urlencode('This reset link has already been used'));
        }

        if (strtotime($reset['expires_at']) < time()) {
            redirect('views/auth/reset_password.php?token=' . urlencode($token) . '&error=' . urlencode('This reset link has expired'));
        }

        // Update password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $stmt->bind_param("si", $hashed_password, $reset['user_id']);
        $stmt->execute();

        // Mark token as used
        $stmt = $conn->prepare("UPDATE password_resets SET used = TRUE WHERE reset_id = ?");
        $stmt->bind_param("i", $reset['reset_id']);
        $stmt->execute();

        // Redirect to login with success message
        redirect('index.php?reset_success=1');
    }
}

// If GET request for showing reset link (demo purposes)
if (isset($_GET['action']) && $_GET['action'] === 'show_reset_link') {
    session_start();
    if (isset($_SESSION['reset_link'])) {
        echo "<!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Password Reset Link</title>
            <link rel='stylesheet' href='../assets/css/style.css'>
        </head>
        <body>
            <div class='auth-container'>
                <div class='auth-card'>
                    <h1>Password Reset Link</h1>
                    <p><strong>Email:</strong> " . htmlspecialchars($_SESSION['reset_email']) . "</p>
                    <p>In production, this link would be sent via email. For demo purposes:</p>
                    <div class='alert alert-info'>
                        <a href='" . htmlspecialchars($_SESSION['reset_link']) . "'>Click here to reset password</a>
                    </div>
                    <p style='word-break: break-all; font-size: 0.9rem; color: var(--text-secondary);'>
                        " . htmlspecialchars($_SESSION['reset_link']) . "
                    </p>
                    <a href='../index.php' class='btn btn-primary btn-block' style='margin-top: 1rem;'>Back to Login</a>
                </div>
            </div>
        </body>
        </html>";
        unset($_SESSION['reset_link']);
        unset($_SESSION['reset_email']);
    } else {
        redirect('views/auth/forgot_password.php');
    }
    exit;
}
?>
