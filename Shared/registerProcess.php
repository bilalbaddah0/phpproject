<?php
// registerProcess.php — handles registration POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $email === '' || $password === '') {
    header('Location: register.php');
    exit;
}

$pdo = include __DIR__ . '/db_connection.php';

// Check for existing username/email
$stmt = $pdo->prepare('SELECT id FROM users WHERE username = :u OR email = :e LIMIT 1');
$stmt->execute([':u' => $username, ':e' => $email]);
if ($stmt->fetch()) {
    // Already exists — in a real app, show message
    header('Location: register.php');
    exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $pdo->prepare('INSERT INTO users (username, email, password) VALUES (:u, :e, :p)');
$stmt->execute([':u' => $username, ':e' => $email, ':p' => $hash]);

header('Location: login.php');
exit;
