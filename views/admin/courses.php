<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/Course.php';

requireRole(ROLE_ADMIN);

$courseModel = new Course();

// Get all courses (not just published)
$db = new Database();
$conn = $db->getConnection();

$sql = "SELECT c.*, cat.category_name, u.full_name as instructor_name,
        (SELECT COUNT(*) FROM enrollments WHERE course_id = c.course_id) as enrollment_count
        FROM courses c 
        LEFT JOIN categories cat ON c.category_id = cat.category_id 
        LEFT JOIN users u ON c.instructor_id = u.user_id 
        ORDER BY c.created_at DESC";

$result = $conn->query($sql);
$courses = [];
while ($row = $result->fetch_assoc()) {
    $courses[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <nav class="navbar">
                <a href="dashboard.php" class="logo"><?php echo SITE_NAME; ?></a>
                <ul class="nav-links">
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="users.php">Users</a></li>
                    <li><a href="courses.php">Courses</a></li>
                    <li><a href="categories.php">Categories</a></li>
                    <li><span>Admin: <?php echo htmlspecialchars($_SESSION['full_name']); ?></span></li>
                    <li>
                        <form action="../../controllers/AuthController.php" method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="logout">
                            <button type="submit" class="btn btn-sm btn-outline">Logout</button>
                        </form>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container" style="margin-top: 2rem;">
        <h1>Manage All Courses</h1>

        <div class="card">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Course Title</th>
                        <th>Instructor</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Enrollments</th>
                        <th>Price</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($courses as $course): ?>
                        <tr>
                            <td><?php echo $course['course_id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($course['title']); ?></strong></td>
                            <td><?php echo htmlspecialchars($course['instructor_name']); ?></td>
                            <td><?php echo htmlspecialchars($course['category_name']); ?></td>
                            <td>
                                <span class="badge badge-<?php 
                                    echo $course['status'] === 'published' ? 'success' : 
                                        ($course['status'] === 'draft' ? 'warning' : 'danger'); 
                                ?>">
                                    <?php echo ucfirst($course['status']); ?>
                                </span>
                            </td>
                            <td><?php echo $course['enrollment_count']; ?></td>
                            <td>$<?php echo number_format($course['price'], 2); ?></td>
                            <td><?php echo formatDate($course['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
