<?php
require_once __DIR__ . '/db_connection.php'; // your connection file
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Please fill in all fields.";
        header("Location: login.php");
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format.";
        header("Location: login.php");
        exit;
    }

    $stmt = $pdo->prepare("SELECT user_id, email, password_hash, full_name, role, admin_status, instructor_status FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['password_hash'])) {
        $_SESSION['error'] = "Invalid email or password.";
        header("Location: login.php");
        exit;
    }

    // Check status for admin
    if ($user['role'] === 'admin') {
        if ($user['admin_status'] === 'pending') {
            $_SESSION['error'] = "Admin approval pending. Please wait for approval.";
            header("Location: login.php");
            exit;
        } elseif ($user['admin_status'] === 'rejected') {
            $_SESSION['error'] = "Admin registration rejected. Contact support.";
            header("Location: login.php");
            exit;
        }
    }

    // Check status for instructor
    if ($user['role'] === 'instructor') {
        if ($user['instructor_status'] === 'pending') {
            $_SESSION['error'] = "Instructor approval pending. Please wait for approval.";
            header("Location: login.php");
            exit;
        } elseif ($user['instructor_status'] === 'rejected') {
            $_SESSION['error'] = "Instructor registration rejected. Contact support.";
            header("Location: login.php");
            exit;
        }
    }

    // Regenerate session id to prevent session fixation
    session_regenerate_id(true);

    // Set session variables
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['email'] = $user['email'];

    // Redirect based on role
    switch (strtolower($user['role'])) {
        case 'admin':
            header("Location: ../Admin/dashboard.php");
            exit;
        case 'instructor':
            header("Location: ../Instructor/dashboard.php");
            exit;
        case 'student':
            header("Location: ../Student/dashboard.php");
            exit;
        default:
            $_SESSION['error'] = "Invalid user role.";
            header("Location: login.php");
            exit;
    }
} else {
    header("Location: login.php");
    exit;
}
?>
