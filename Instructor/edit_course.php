<?php
session_start();
require_once __DIR__ . '/../Shared/db_connection.php';
// Ensure instructor is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'instructor') {
    header('Location: ../Shared/login.php');
    exit;
}
$instructor_id = $_SESSION['user_id'];
$course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$course_id) {
    $_SESSION['error'] = 'No course specified.';
    header('Location: courses.php');
    exit;
}
// Fetch categories
$catStmt = $pdo->query("SELECT category_id, category_name FROM categories ORDER BY category_name ASC");
$categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);
// Fetch course (must belong to instructor)
$stmt = $pdo->prepare("SELECT * FROM courses WHERE course_id = ? AND instructor_id = ?");
$stmt->execute([$course_id, $instructor_id]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$course) {
    $_SESSION['error'] = 'Course not found or not yours.';
    header('Location: courses.php');
    exit;
}
// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category_id = intval($_POST['category_id'] ?? 0);
    $price = floatval($_POST['price'] ?? 0);
    $level = trim($_POST['level'] ?? 'beginner');
    if ($title && $description && $category_id && $level) {
        $update = $pdo->prepare("UPDATE courses SET title=?, description=?, category_id=?, price=?, level=? WHERE course_id=? AND instructor_id=?");
        $ok = $update->execute([$title, $description, $category_id, $price, $level, $course_id, $instructor_id]);
        if ($ok) {
            $_SESSION['success'] = 'Course updated successfully!';
            header('Location: edit_course.php?id=' . $course_id);
            exit;
        } else {
            $_SESSION['error'] = 'Failed to update course.';
        }
    } else {
        $_SESSION['error'] = 'Please fill in all required fields.';
    }
    // Refresh course data after update attempt
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE course_id = ? AND instructor_id = ?");
    $stmt->execute([$course_id, $instructor_id]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Course - LMS</title>
    <link rel="stylesheet" href="../assets/css/style.css">
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
        <div style="max-width: 800px; margin: 0 auto;">
            <h1 style="margin-bottom: 2rem;">Edit Course</h1>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <form method="POST">
                    <div class="form-group">
                        <label class="form-label">Course Title *</label>
                           <input type="text" name="title" class="form-control" required 
                               value="<?php echo htmlspecialchars($course['title'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Course Description *</label>
                        <textarea name="description" class="form-control" required><?php echo htmlspecialchars($course['description'] ?? ''); ?></textarea>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label">Category *</label>
                            <select name="category_id" class="form-control" required>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['category_id']; ?>"
                                            <?php echo (isset($course['category_id']) && $course['category_id'] == $category['category_id']) ? 'selected' : ''; ?>
                                        >
                                        <?php echo htmlspecialchars($category['category_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Level *</label>
                            <select name="level" class="form-control" required>
                                <option value="beginner" <?php echo (isset($course['level']) && $course['level'] === 'beginner') ? 'selected' : ''; ?>>Beginner</option>
                                <option value="intermediate" <?php echo (isset($course['level']) && $course['level'] === 'intermediate') ? 'selected' : ''; ?>>Intermediate</option>
                                <option value="advanced" <?php echo (isset($course['level']) && $course['level'] === 'advanced') ? 'selected' : ''; ?>>Advanced</option>
                            </select>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label">Price ($)</label>
                            <input type="number" name="price" class="form-control" step="0.01" min="0" 
                                value="<?php echo isset($course['price']) ? $course['price'] : '0'; ?>">
                        </div>

                        <!-- No status field: approval_status is managed by admin -->
                    </div>

                    <div style="display: flex; gap: 1rem;">
                        <button type="submit" class="btn btn-primary">Update Course</button>
                        <a href="dashboard.php" class="btn btn-outline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>