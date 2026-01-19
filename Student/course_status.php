<?php
session_start();
require_once __DIR__ . '/../Shared/db_connection.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'student') {
    $_SESSION['error'] = 'You must be logged in as a student to perform that action.';
    header('Location: ../Shared/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['course_id']) || !isset($_POST['completed'])) {
    $_SESSION['error'] = 'Invalid request.';
    header('Location: browse.php');
    exit;
}

$student_id = $_SESSION['user_id'];
$course_id = intval($_POST['course_id']);
$completed = intval($_POST['completed']) === 1 ? 1 : 0;

$stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM enrollments WHERE student_id = ? AND course_id = ?");
$stmt->execute([$student_id, $course_id]);
$r = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$r || $r['cnt'] == 0) {
    $_SESSION['error'] = 'You must be enrolled in the course to change its status.';
    header('Location: browse.php');
    exit;
}

$up = $pdo->prepare("INSERT INTO student_course_status (student_id, course_id, is_completed) VALUES (?, ?, ?) 
    ON DUPLICATE KEY UPDATE is_completed = VALUES(is_completed), updated_at = NOW()");
if ($up->execute([$student_id, $course_id, $completed])) {
    if ($completed) {
        $_SESSION['success'] = 'Marked as completed.';
    } else {
        $_SESSION['success'] = 'Marked as not completed.';
    }
} else {
    $_SESSION['error'] = 'Failed to update status. Please try again.';
}

$redirect = $_SERVER['HTTP_REFERER'] ?? 'browse.php';
header('Location: ' . $redirect);
exit;
?>