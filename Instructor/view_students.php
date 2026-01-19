<?php
session_start();
require_once __DIR__ . '/../Shared/db_connection.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'instructor') {
    header('Location: ../Shared/login.php');
    exit;
}

$instructor_id = $_SESSION['user_id'];
$course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$course_check = $pdo->prepare("SELECT course_id, title FROM courses WHERE course_id = ? AND instructor_id = ?");
$course_check->execute([$course_id, $instructor_id]);
$course = $course_check->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    $_SESSION['error'] = 'Course not found or you do not have access.';
    header('Location: courses.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT u.user_id, u.full_name, u.email, e.enrollment_id, 
           (SELECT is_completed FROM student_course_status WHERE student_id = u.user_id AND course_id = ?) AS is_completed
    FROM enrollments e
    JOIN users u ON e.student_id = u.user_id
    WHERE e.course_id = ?
    ORDER BY u.full_name ASC
");
$stmt->execute([$course_id, $course_id]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrolled Students - <?php echo htmlspecialchars($course['title']); ?> - LMS</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #111827;
            background-color: #F9FAFB;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        header {
            background-color: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: #4F46E5;
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 2rem;
            align-items: center;
        }

        .nav-links a {
            text-decoration: none;
            color: #111827;
            font-weight: 500;
            transition: color 0.3s;
        }

        .nav-links a:hover {
            color: #4F46E5;
        }

        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s;
        }

        .btn-primary {
            background-color: #4F46E5;
            color: white;
        }

        .btn-primary:hover {
            background-color: #4338CA;
        }

        .btn-secondary {
            background-color: #10B981;
            color: white;
        }

        .btn-outline {
            background-color: transparent;
            border: 2px solid #4F46E5;
            color: #4F46E5;
        }

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }

        .card {
            background: white;
            border-radius: 0.75rem;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .table th,
        .table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #E5E7EB;
        }

        .table th {
            background-color: #F9FAFB;
            font-weight: 600;
            color: #111827;
        }

        .table tr:hover {
            background-color: #F9FAFB;
        }

        .badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 0.25rem;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .badge-completed {
            background-color: #DBEAFE;
            color: #1E40AF;
        }

        .badge-not-completed {
            background-color: #FED7AA;
            color: #92400E;
        }

        .text-secondary {
            color: #6B7280;
        }

        .header-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .course-title-section h1 {
            margin-bottom: 0.5rem;
        }

        .course-title-section p {
            color: #6B7280;
            font-size: 0.95rem;
        }

        .stats {
            display: flex;
            gap: 2rem;
            margin-bottom: 1.5rem;
        }

        .stat-box {
            background: white;
            padding: 1.5rem;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            flex: 1;
        }

        .stat-box h3 {
            color: #6B7280;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .stat-box p {
            font-size: 2rem;
            font-weight: bold;
            color: #4F46E5;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <nav class="navbar">
                <a href="dashboard.php" class="logo">LMS</a>
                <ul class="nav-links">
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="courses.php">My Courses</a></li>
                    <li><span><?php echo htmlspecialchars($_SESSION['full_name']); ?></span></li>
                    <li>
                        <form action="../Shared/logout.php" method="POST" style="display: inline;">
                            <button type="submit" class="btn btn-sm btn-outline">Logout</button>
                        </form>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container" style="margin-top: 2rem;">
        <div class="header-info">
            <div class="course-title-section">
                <h1><?php echo htmlspecialchars($course['title']); ?></h1>
                <p>Enrolled Students</p>
            </div>
            <a href="courses.php" class="btn btn-primary">Back to Courses</a>
        </div>

        <div class="stats">
            <div class="stat-box">
                <h3>Total Enrolled</h3>
                <p><?php echo count($students); ?></p>
            </div>
            <div class="stat-box">
                <h3>Completed</h3>
                <p><?php echo count(array_filter($students, function($s) { return (int)($s['is_completed'] ?? 0) === 1; })); ?></p>
            </div>
        </div>

        <div class="card">
            <?php if (empty($students)): ?>
                <p style="text-align: center; color: #6B7280; padding: 2rem;">No students enrolled in this course yet.</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($student['email']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo ((int)($student['is_completed'] ?? 0) === 1) ? 'completed' : 'not-completed'; ?>">
                                        <?php echo ((int)($student['is_completed'] ?? 0) === 1) ? 'Completed' : 'In Progress'; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
