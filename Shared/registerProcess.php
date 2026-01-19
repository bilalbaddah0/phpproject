<?php
// registerProcess.php — handles registration POST
session_start();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request.';
    header('Location: register.php');
    exit;
}

$full_name = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password_hash = trim($_POST['password'] ?? '');
$role = $_POST['role'] ?? '';

if ($full_name === '' || $email === '' || $password === '' || !in_array($role, ['admin', 'instructor', 'student'])) {
    $_SESSION['error'] = 'Please fill all fields and select a valid role.';
    header('Location: register.php');
    exit;
}

require_once __DIR__ . '/db_connection.php';

// Check for existing email
$stmt = $pdo->prepare('SELECT user_id FROM users WHERE email = :e LIMIT 1');
$stmt->execute([':e' => $email]);
if ($stmt->fetch()) {
    $_SESSION['error'] = 'Email already registered.';
    header('Location: register.php');
    exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);
try {
    $stmt = $pdo->prepare('INSERT INTO users (full_name, email, password_hash, role) VALUES (:n, :e, :p, :r)');
    $stmt->execute([':n' => $full_name, ':e' => $email, ':p' => $hash, ':r' => $role]);
    $_SESSION['success'] = 'Registration successful. Please login.';
    header('Location: login.php');
    exit;
} catch (Exception $e) {
    $_SESSION['error'] = 'Registration failed. Please try again.';
    header('Location: register.php');
    exit;
}
