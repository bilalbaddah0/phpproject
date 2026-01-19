<?php
session_start();
require_once __DIR__ . '/../Shared/db_connection.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'instructor') {
    header('Location: ../Shared/login.php');
    exit;
}
$instructor_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_course_id'])) {
    $delete_id = intval($_POST['delete_course_id']);
    $check = $pdo->prepare("SELECT COUNT(*) AS cnt FROM courses WHERE course_id = ? AND instructor_id = ?");
    $check->execute([$delete_id, $instructor_id]);
    $row = $check->fetch();
    $enroll = $pdo->prepare("SELECT COUNT(*) AS cnt FROM enrollments WHERE course_id = ?");
    $enroll->execute([$delete_id]);
    $enrollRow = $enroll->fetch();
    if (($row['cnt'] ?? 0) > 0 && ($enrollRow['cnt'] ?? 1) == 0) {
        $del = $pdo->prepare("DELETE FROM courses WHERE course_id = ?");
        $del->execute([$delete_id]);
        $_SESSION['success'] = 'Course deleted.';
        header('Location: courses.php');
        exit;
    } else {
        $_SESSION['error'] = 'Cannot delete: either not your course or students are enrolled.';
        header('Location: courses.php');
        exit;
    }
}

$stmt = $pdo->prepare("SELECT c.course_id, c.title, c.description, c.price, c.level, c.approval_status, cat.category_name,
    (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.course_id) AS enrollment_count
    FROM courses c
    LEFT JOIN categories cat ON c.category_id = cat.category_id
    WHERE c.instructor_id = ?");
$stmt->execute([$instructor_id]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses - LMS</title>
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

        .btn-danger {
            background-color: #EF4444;
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

        .text-secondary {
            color: #6B7280;
        }

        .description-cell {
            max-width: 250px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            cursor: pointer;
            color: #4F46E5;
            transition: all 0.2s;
        }

        .description-cell:hover {
            text-decoration: underline;
            color: #4338CA;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            animation: fadeIn 0.3s;
        }

        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            background-color: white;
            padding: 2rem;
            border-radius: 0.75rem;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 20px 25px rgba(0,0,0,0.15);
            animation: slideUp 0.3s;
        }

        @keyframes slideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            border-bottom: 1px solid #E5E7EB;
            padding-bottom: 1rem;
        }

        .modal-header h2 {
            margin: 0;
            color: #111827;
        }

        .close-btn {
            font-size: 1.5rem;
            font-weight: bold;
            cursor: pointer;
            color: #6B7280;
            background: none;
            border: none;
            padding: 0;
            transition: color 0.2s;
        }

        .close-btn:hover {
            color: #111827;
        }

        .modal-body {
            color: #374151;
            line-height: 1.8;
            word-wrap: break-word;
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
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1>My Courses</h1>
            <a href="create_course.php" class="btn btn-primary">+ Create New Course</a>
        </div>

        <div class="card">
            <?php if (empty($courses)): ?>
                <p style="text-align: center; color: #6B7280; padding: 2rem;">You have no courses yet.</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Level</th>
                            <th>Approval</th>
                            <th>Enrollments</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($courses as $course): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($course['title'] ?? ''); ?></td>
                                <td class="description-cell" onclick="openModal(<?php echo (int)$course['course_id']; ?>, '<?php echo htmlspecialchars(addslashes($course['title'] ?? ''), ENT_QUOTES); ?>', '<?php echo htmlspecialchars(addslashes($course['description'] ?? ''), ENT_QUOTES); ?>')" title="Click to view full description"><?php echo htmlspecialchars($course['description'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($course['category_name'] ?? '-'); ?></td>
                                <td>$<?php echo number_format((float)($course['price'] ?? 0), 2); ?></td>
                                <td><?php echo htmlspecialchars(ucfirst($course['level'] ?? '-')); ?></td>
                                <td>
                                    <span class="badge badge-<?php 
                                        echo ($course['approval_status'] ?? '') === 'approved' ? 'success' : 
                                            (($course['approval_status'] ?? '') === 'pending' ? 'warning' : 'danger'); 
                                    ?>">
                                        <?php echo htmlspecialchars(ucfirst($course['approval_status'] ?? '-')); ?>
                                    </span>
                                </td>
                                <td><?php echo (int)($course['enrollment_count'] ?? 0); ?></td>
                                <td>
                                    <div style="display: flex; gap: 0.25rem; align-items: center; white-space: nowrap;">
                                        <a href="edit_course.php?id=<?php echo (int)$course['course_id']; ?>" class="btn btn-sm btn-secondary">Edit</a>
                                        <?php if ((int)($course['enrollment_count'] ?? 0) > 0): ?>
                                            <a href="view_students.php?id=<?php echo (int)$course['course_id']; ?>" class="btn btn-sm" style="background-color: #8B5CF6; color: white;">View</a>
                                        <?php endif; ?>
                                        <?php if ((int)($course['enrollment_count'] ?? 0) === 0): ?>
                                            <form method="POST" style="display:inline; margin:0;" onsubmit="return confirm('Delete this course?');">
                                                <input type="hidden" name="delete_course_id" value="<?php echo (int)$course['course_id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                            </form>
                                        <?php else: ?>
                                            <span style="color: #aaa; font-size: 0.95em;">Cannot delete</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <div id="descriptionModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle"></h2>
                <button class="close-btn" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <p id="modalDescription"></p>
            </div>
        </div>
    </div>

    <script>
        function openModal(courseId, title, description) {
            document.getElementById('modalTitle').textContent = title;
            document.getElementById('modalDescription').textContent = description;
            document.getElementById('descriptionModal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('descriptionModal').classList.remove('active');
        }

        document.getElementById('descriptionModal').addEventListener('click', function(event) {
            if (event.target === this) {
                closeModal();
            }
        });

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeModal();
            }
        });
    </script>
</body>
</html>
