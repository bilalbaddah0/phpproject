<?php
// General Configuration
define('SITE_NAME', 'E-Learning Management System');
define('BASE_URL', 'http://localhost:8080/php/lms/');
define('UPLOAD_DIR', __DIR__ . '/../assets/uploads/');

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Timezone
date_default_timezone_set('UTC');

// Error Reporting (for development)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// User Roles
define('ROLE_ADMIN', 'admin');
define('ROLE_INSTRUCTOR', 'instructor');
define('ROLE_STUDENT', 'student');

// Helper Functions
function redirect($url) {
    header("Location: " . BASE_URL . $url);
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect('index.php');
    }
}

function requireRole($role) {
    requireLogin();
    if ($_SESSION['role'] !== $role) {
        redirect('index.php');
    }
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function formatDate($date) {
    return date('F j, Y', strtotime($date));
}

function calculateProgress($completed, $total) {
    if ($total == 0) return 0;
    return round(($completed / $total) * 100);
}
?>
