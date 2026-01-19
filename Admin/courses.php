<?php
session_start();

// Basic admin check
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    header('Location: ../Shared/login.php');
    exit;
}

require_once __DIR__ . '/../Shared/db_connection.php';

// Handle course update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_course'])) {
        $course_id = intval($_POST['course_id']);
        $title = trim($_POST['title']);
        $price = floatval($_POST['price']);
        $level = trim($_POST['level']);
        $category_id = intval($_POST['category_id']);

        $stmt = $pdo->prepare("UPDATE courses SET title = ?, price = ?, level = ?, category_id = ? WHERE course_id = ?");
        if ($stmt->execute([$title, $price, $level, $category_id, $course_id])) {
            $_SESSION['success'] = "Course updated successfully!";
        } else {
            $_SESSION['error'] = "Failed to update course.";
        }
        header('Location: courses.php');
        exit;
    }

    if (isset($_POST['delete_course_id'])) {
        $course_id = intval($_POST['delete_course_id']);
        $stmt = $pdo->prepare("DELETE FROM courses WHERE course_id = ?");
        if ($stmt->execute([$course_id])) {
            $_SESSION['success'] = "Course deleted successfully!";
        } else {
            $_SESSION['error'] = "Failed to delete course.";
        }
        header('Location: courses.php');
        exit;
    }
}

// Get all courses
$stmt = $pdo->query("SELECT c.course_id, c.title, c.price, c.level, c.category_id, cat.category_name, u.full_name AS instructor_name,
    (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.course_id) AS enrollment_count
    FROM courses c
    LEFT JOIN categories cat ON c.category_id = cat.category_id
    LEFT JOIN users u ON c.instructor_id = u.user_id");
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all categories for dropdown
$cat_stmt = $pdo->query("SELECT category_id, category_name FROM categories");
$categories = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);
$category_options = [];
foreach ($categories as $cat) {
    $category_options[$cat['category_id']] = $cat['category_name'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Courses - LMS</title>
<link rel="stylesheet" href="../assets/css/style.css">
<style>
    .btn-row { display: flex; gap: 0.5rem; }
</style>
</head>
<body>
<header>
    <div class="container">
        <nav class="navbar">
            <a href="dashboard.php" class="logo">LMS</a>
            <ul class="nav-links">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="users.php">Users</a></li>
                <li><a href="courses.php">Courses</a></li>
                <li><a href="course_approvals.php">Approvals</a></li>
                <li><a href="categories.php">Categories</a></li>
                <li><span><?php echo htmlspecialchars($_SESSION['full_name']); ?></span></li>
                <li>
                    <form action="../Shared/logout.php" method="POST" style="display:inline;">
                        <button type="submit" class="btn btn-sm btn-outline">Logout</button>
                    </form>
                </li>
            </ul>
        </nav>
    </div>
</header>

<div class="container" style="margin-top: 2rem;">
    <h1>Manage All Courses</h1>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="card">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Instructor</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Enrollments</th>
                    <th>Price</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($courses as $course): ?>
                <tr>
                    <td><?php echo $course['course_id']; ?></td>
                    <td>
                        <?php if(isset($_GET['edit']) && $_GET['edit'] == $course['course_id']): ?>
                            <input type="text" form="edit-<?php echo $course['course_id']; ?>" name="title" value="<?php echo htmlspecialchars($course['title']); ?>" required>
                        <?php else: ?>
                            <?php echo htmlspecialchars($course['title']); ?>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($course['instructor_name']); ?></td>
                    <td>
                        <?php if(isset($_GET['edit']) && $_GET['edit'] == $course['course_id']): ?>
                            <select form="edit-<?php echo $course['course_id']; ?>" name="category_id" required>
                                <?php foreach ($category_options as $id => $name): ?>
                                    <option value="<?php echo $id; ?>" <?php echo $id == $course['category_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php else: ?>
                            <?php echo htmlspecialchars($course['category_name']); ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if(isset($_GET['edit']) && $_GET['edit'] == $course['course_id']): ?>
                            <input type="text" form="edit-<?php echo $course['course_id']; ?>" name="level" value="<?php echo htmlspecialchars($course['level']); ?>" required>
                        <?php else: ?>
                            <span class="badge badge-info"><?php echo htmlspecialchars(ucfirst($course['level'] ?? 'N/A')); ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo (int)$course['enrollment_count']; ?></td>
                    <td>
                        <?php if(isset($_GET['edit']) && $_GET['edit'] == $course['course_id']): ?>
                            <input type="number" form="edit-<?php echo $course['course_id']; ?>" name="price" value="<?php echo $course['price']; ?>" step="0.01" required>
                        <?php else: ?>
                            $<?php echo number_format($course['price'], 2); ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="btn-row">
                        <?php if(isset($_GET['edit']) && $_GET['edit'] == $course['course_id']): ?>
                            <form id="edit-<?php echo $course['course_id']; ?>" method="POST" style="display:flex; gap:0.5rem;">
                                <input type="hidden" name="course_id" value="<?php echo $course['course_id']; ?>">
                                <button type="submit" name="save_course" class="btn btn-sm btn-primary">Save</button>
                                <a href="courses.php" class="btn btn-sm btn-outline">Cancel</a>
                            </form>
                        <?php else: ?>
                            <a href="?edit=<?php echo $course['course_id']; ?>" class="btn btn-sm btn-outline">Edit</a>
                            <form method="POST" style="margin:0;" onsubmit="return confirm('Delete this course?');">
                                <input type="hidden" name="delete_course_id" value="<?php echo $course['course_id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
