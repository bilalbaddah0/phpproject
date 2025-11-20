<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/Course.php';

requireRole(ROLE_INSTRUCTOR);

$course_id = intval($_GET['id'] ?? 0);
$courseModel = new Course();
$course = $courseModel->getCourseById($course_id);

// Verify ownership
if (!$course || $course['instructor_id'] != $_SESSION['user_id']) {
    redirect('views/instructor/dashboard.php');
}

$categories = $courseModel->getAllCategories();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitizeInput($_POST['title']);
    $description = sanitizeInput($_POST['description']);
    $category_id = intval($_POST['category_id']);
    $price = floatval($_POST['price']);
    $level = sanitizeInput($_POST['level']);
    $status = sanitizeInput($_POST['status']);

    if ($courseModel->updateCourse($course_id, $title, $description, $category_id, $price, $level, $status)) {
        $_SESSION['success'] = 'Course updated successfully!';
        redirect('views/instructor/edit_course.php?id=' . $course_id);
    } else {
        $_SESSION['error'] = 'Failed to update course';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Course - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <nav class="navbar">
                <a href="dashboard.php" class="logo"><?php echo SITE_NAME; ?></a>
                <ul class="nav-links">
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="courses.php">My Courses</a></li>
                    <li><span><?php echo htmlspecialchars($_SESSION['full_name']); ?></span></li>
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
                               value="<?php echo htmlspecialchars($course['title']); ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Course Description *</label>
                        <textarea name="description" class="form-control" required><?php echo htmlspecialchars($course['description']); ?></textarea>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label">Category *</label>
                            <select name="category_id" class="form-control" required>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['category_id']; ?>"
                                            <?php echo $course['category_id'] == $category['category_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['category_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Level *</label>
                            <select name="level" class="form-control" required>
                                <option value="beginner" <?php echo $course['level'] === 'beginner' ? 'selected' : ''; ?>>Beginner</option>
                                <option value="intermediate" <?php echo $course['level'] === 'intermediate' ? 'selected' : ''; ?>>Intermediate</option>
                                <option value="advanced" <?php echo $course['level'] === 'advanced' ? 'selected' : ''; ?>>Advanced</option>
                            </select>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label">Price ($)</label>
                            <input type="number" name="price" class="form-control" step="0.01" min="0" 
                                   value="<?php echo $course['price']; ?>">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Status *</label>
                            <select name="status" class="form-control" required>
                                <option value="draft" <?php echo $course['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                <option value="published" <?php echo $course['status'] === 'published' ? 'selected' : ''; ?>>Published</option>
                                <option value="archived" <?php echo $course['status'] === 'archived' ? 'selected' : ''; ?>>Archived</option>
                            </select>
                        </div>
                    </div>

                    <div style="display: flex; gap: 1rem;">
                        <button type="submit" class="btn btn-primary">Update Course</button>
                        <a href="manage_content.php?id=<?php echo $course_id; ?>" class="btn btn-secondary">Manage Content</a>
                        <a href="dashboard.php" class="btn btn-outline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
