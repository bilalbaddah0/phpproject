<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'login') {
        $email = sanitizeInput($_POST['email']);
        $password = $_POST['password'];

        if (empty($email) || empty($password)) {
            $_SESSION['error'] = 'Please fill in all fields';
            redirect('index.php');
        }

        $user = new User();
        $result = $user->login($email, $password);

        if ($result['success']) {
            // Redirect based on role
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
                default:
                    redirect('index.php');
            }
        } else {
            $_SESSION['error'] = $result['message'];
            redirect('index.php');
        }
    } elseif ($action === 'register') {
        $email = sanitizeInput($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $full_name = sanitizeInput($_POST['full_name']);
        $role = sanitizeInput($_POST['role'] ?? 'student');

        // Validation
        if (empty($email) || empty($password) || empty($full_name)) {
            $_SESSION['error'] = 'Please fill in all required fields';
            redirect('views/auth/register.php');
        }

        if ($password !== $confirm_password) {
            $_SESSION['error'] = 'Passwords do not match';
            redirect('views/auth/register.php');
        }

        if (strlen($password) < 6) {
            $_SESSION['error'] = 'Password must be at least 6 characters';
            redirect('views/auth/register.php');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Invalid email format';
            redirect('views/auth/register.php');
        }

        // Only allow student and instructor registration (admin created manually)
        if (!in_array($role, ['student', 'instructor'])) {
            $role = 'student';
        }

        $user = new User();
        $result = $user->register($email, $password, $full_name, $role);

        if ($result['success']) {
            $_SESSION['success'] = 'Registration successful! Please login.';
            redirect('index.php');
        } else {
            $_SESSION['error'] = $result['message'];
            redirect('views/auth/register.php');
        }
    } elseif ($action === 'logout') {
        User::logout();
        redirect('index.php');
    }
} else {
    redirect('index.php');
}
?>
