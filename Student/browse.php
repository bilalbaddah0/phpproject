<?php
session_start();

// Require DB and ensure student is logged in
require_once __DIR__ . '/../Shared/db_connection.php';
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'student') {
    header('Location: ../Shared/login.php');
    exit;
}

// Helper to check enrollment
function isEnrolledPDO($pdo, $student_id, $course_id) {
    $s = $pdo->prepare("SELECT COUNT(*) as cnt FROM enrollments WHERE student_id = ? AND course_id = ?");
    $s->execute([$student_id, $course_id]);
    $r = $s->fetch(PDO::FETCH_ASSOC);
    return ($r && $r['cnt'] > 0);
}

// Get search and filter parameters
$search = $_GET['search'] ?? null;
$category_id = $_GET['category'] ?? null;

// Fetch categories
$catStmt = $pdo->query("SELECT category_id, category_name FROM categories ORDER BY category_name ASC");
$categories = $catStmt ? $catStmt->fetchAll(PDO::FETCH_ASSOC) : [];

// Build courses query
$params = [];
$sql = "SELECT c.course_id, c.title, c.description, c.price, c.level, cat.category_name, u.full_name AS instructor_name, 
    (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.course_id) AS enrollment_count
    FROM courses c
    LEFT JOIN categories cat ON c.category_id = cat.category_id
    LEFT JOIN users u ON c.instructor_id = u.user_id";

$where = [];
if (!empty($search)) {
    $where[] = "(c.title LIKE ? OR c.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if (!empty($category_id)) {
    $where[] = "c.category_id = ?";
    $params[] = $category_id;
}
if (!empty($where)) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY c.course_id DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Helper to check if student marked course completed
function isCourseCompletedPDO($pdo, $student_id, $course_id) {
    $s = $pdo->prepare("SELECT is_completed FROM student_course_status WHERE student_id = ? AND course_id = ? LIMIT 1");
    $s->execute([$student_id, $course_id]);
    $r = $s->fetch(PDO::FETCH_ASSOC);
    return ($r && $r['is_completed'] == 1);
}

// Handle enrollment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enroll'])) {
    $course_id = intval($_POST['course_id']);
    $student_id = $_SESSION['user_id'];

    if (isEnrolledPDO($pdo, $student_id, $course_id)) {
        $_SESSION['error'] = 'You are already enrolled in this course.';
        header('Location: browse.php');
        exit;
    }

    $ins = $pdo->prepare("INSERT INTO enrollments (student_id, course_id) VALUES (?, ?)");
    if ($ins->execute([$student_id, $course_id])) {
        // Initialize tracking record (not completed by default)
        $up = $pdo->prepare("INSERT INTO student_course_status (student_id, course_id, is_completed) VALUES (?, ?, 0) ON DUPLICATE KEY UPDATE student_id = student_id");
        $up->execute([$student_id, $course_id]);

        $_SESSION['success'] = 'Successfully enrolled in the course!';
        header('Location: browse.php');
        exit;
    } else {
        $_SESSION['error'] = 'Failed to enroll. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Courses - LMS</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <nav class="navbar">
                <a href="dashboard.php" class="logo">LMS</a>
                <ul class="nav-links">
                    <li><a href="dashboard.php">My Learning</a></li>
                    <li><a href="browse.php">Browse Courses</a></li>
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
        <h1>Browse All Courses</h1>

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

        <div class="card mb-3">
            <form method="GET" action="">
                <div style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 1rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <input type="text" name="search" class="form-control" placeholder="Search courses..." 
                               value="<?php echo htmlspecialchars($search ?? ''); ?>">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <select name="category" class="form-control">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['category_id']; ?>" 
                                        <?php echo $category_id == $cat['category_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['category_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Search</button>
                </div>
            </form>
        </div>

        <?php if (empty($courses)): ?>
            <div class="card">
                <p style="text-align: center; color: var(--text-secondary); padding: 2rem;">
                    No courses found. Try adjusting your search criteria.
                </p>
            </div>
        <?php else: ?>
            <div class="course-grid">
                <?php foreach ($courses as $course): ?>
                    <?php 
                    $isEnrolled = isEnrolledPDO($pdo, $_SESSION['user_id'], $course['course_id']);
                    ?>
                    <div class="course-card">
                        <div class="course-thumbnail">📚</div>
                        <div class="course-body">
                            <h3 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h3>
                            <p class="course-instructor">👨‍🏫 <?php echo htmlspecialchars($course['instructor_name']); ?></p>
                            <p style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 1rem;">
                                <?php echo htmlspecialchars(substr($course['description'], 0, 100)) . '...'; ?>
                            </p>
                            
                            <div style="display: flex; gap: 0.5rem; margin-bottom: 1rem;">
                                <span class="badge badge-primary"><?php echo htmlspecialchars($course['category_name']); ?></span>
                                <span class="badge badge-warning"><?php echo ucfirst($course['level']); ?></span>
                            </div>

                            <div class="course-meta">
                                <div>
                                    <div style="font-size: 0.875rem; color: var(--text-secondary);">
                                        <?php echo $course['enrollment_count']; ?> students
                                    </div>
                                </div>
                                
                                <?php if ($isEnrolled): ?>
                                    <?php $isCompleted = isCourseCompletedPDO($pdo, $_SESSION['user_id'], $course['course_id']); ?>
                                    <?php if ($isCompleted): ?>
                                        <form method="POST" action="course_status.php" style="margin:0; display:inline;">
                                            <input type="hidden" name="course_id" value="<?php echo $course['course_id']; ?>">
                                            <input type="hidden" name="completed" value="0">
                                            <button type="submit" class="btn btn-sm btn-outline">Mark as not done</button>
                                        </form>
                                        <span class="badge badge-success" style="margin-left:0.5rem;">Completed</span>
                                    <?php else: ?>
                                        <form method="POST" action="course_status.php" style="margin:0; display:inline;">
                                            <input type="hidden" name="course_id" value="<?php echo $course['course_id']; ?>">
                                            <input type="hidden" name="completed" value="1">
                                            <button type="submit" class="btn btn-sm btn-primary">Mark as done</button>
                                        </form>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <form method="POST" style="margin: 0;">
                                        <input type="hidden" name="course_id" value="<?php echo $course['course_id']; ?>">
                                        <button type="submit" name="enroll" class="btn btn-sm btn-primary">
                                            Enroll Now
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>