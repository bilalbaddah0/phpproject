<?php
session_start();

// Basic admin check
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    header('Location: ../Shared/login.php');
    exit;
}

require_once __DIR__ . '/../Shared/db_connection.php';

// Ensure approval-related columns exist (add if missing)
try {
    $pdo->exec("ALTER TABLE courses ADD COLUMN IF NOT EXISTS approval_status ENUM('pending','approved','rejected') DEFAULT 'pending'");
    $pdo->exec("ALTER TABLE courses ADD COLUMN IF NOT EXISTS approved_by INT NULL");
    $pdo->exec("ALTER TABLE courses ADD COLUMN IF NOT EXISTS approved_at DATETIME NULL");
    $pdo->exec("ALTER TABLE courses ADD COLUMN IF NOT EXISTS rejection_reason TEXT NULL");
} catch (PDOException $e) {
    // Ignore
}

// Handle approval actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $course_id = (int)($_POST['course_id'] ?? 0);

    if ($action === 'approve') {
        $stmt = $pdo->prepare("UPDATE courses SET approval_status = 'approved', approved_by = ?, approved_at = NOW() WHERE course_id = ?");
        $stmt->execute([$_SESSION['user_id'], $course_id]);
        $_SESSION['success'] = 'Course approved successfully!';
    } elseif ($action === 'reject') {
        $rejection_reason = trim($_POST['rejection_reason'] ?? 'Not specified');
        $stmt = $pdo->prepare("UPDATE courses SET approval_status = 'rejected', approved_by = ?, approved_at = NOW(), rejection_reason = ? WHERE course_id = ?");
        $stmt->execute([$_SESSION['user_id'], $rejection_reason, $course_id]);
        $_SESSION['success'] = 'Course rejected.';
    }

    header('Location: course_approvals.php');
    exit;
}

// Get filter
$filter = $_GET['filter'] ?? 'pending';

// Build query
$baseSql = "SELECT c.course_id, c.title, c.description, c.price, c.level, c.approval_status, c.rejection_reason,
        cat.category_name, u.full_name as instructor_name,
        (SELECT COUNT(*) FROM enrollments WHERE course_id = c.course_id) as enrollment_count
        FROM courses c
        LEFT JOIN categories cat ON c.category_id = cat.category_id
        LEFT JOIN users u ON c.instructor_id = u.user_id";

if ($filter === 'pending') $baseSql .= " WHERE c.approval_status = 'pending'";
elseif ($filter === 'approved') $baseSql .= " WHERE c.approval_status = 'approved'";
elseif ($filter === 'rejected') $baseSql .= " WHERE c.approval_status = 'rejected'";

$stmt = $pdo->query($baseSql);
$courses = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

// Count by status
$status_counts = [];
$countStmt = $pdo->query("SELECT approval_status, COUNT(*) as count FROM courses GROUP BY approval_status");
if ($countStmt) {
    foreach ($countStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $status_counts[$row['approval_status']] = (int)$row['count'];
    }
}

// Date formatter
if (!function_exists('formatDate')) {
    function formatDate($d) {
        $ts = strtotime($d);
        return $ts === false ? 'N/A' : date('Y-m-d', $ts);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Course Approvals - LMS</title>
<link rel="stylesheet" href="../assets/css/style.css">
<style>
    .filter-tabs { display: flex; gap: 1rem; margin-bottom: 2rem; border-bottom: 2px solid #ddd; }
    .filter-tab { padding: 0.5rem 1rem; text-decoration: none; color: #555; border-bottom: 3px solid transparent; }
    .filter-tab.active { color: #4F46E5; border-bottom-color: #4F46E5; font-weight: 600; }
    .course-card { border: 1px solid #ddd; border-radius: 0.5rem; padding: 1rem; margin-bottom: 1rem; background: #fff; }
    .course-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem; }
    .course-actions { display: flex; gap: 0.5rem; margin-top: 0.5rem; }
    .rejection-form { display: none; margin-top: 0.5rem; background: #f9fafb; padding: 1rem; border-radius: 0.5rem; }
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

<div class="container" style="margin-top:2rem;">
    <h1>Course Approvals</h1>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <div class="filter-tabs">
        <a href="?filter=pending" class="filter-tab <?php echo $filter==='pending'?'active':''; ?>">Pending (<?php echo $status_counts['pending']??0; ?>)</a>
        <a href="?filter=approved" class="filter-tab <?php echo $filter==='approved'?'active':''; ?>">Approved (<?php echo $status_counts['approved']??0; ?>)</a>
        <a href="?filter=rejected" class="filter-tab <?php echo $filter==='rejected'?'active':''; ?>">Rejected (<?php echo $status_counts['rejected']??0; ?>)</a>
        <a href="?filter=all" class="filter-tab <?php echo $filter==='all'?'active':''; ?>">All Courses</a>
    </div>

    <?php if (empty($courses)): ?>
        <p style="text-align:center; color:#6B7280; padding:2rem;">No courses found.</p>
    <?php else: ?>
        <?php foreach ($courses as $course): ?>
            <div class="course-card">
                <div class="course-header">
                    <div>
                        <h3 style="margin:0 0 0.5rem 0;"><?php echo htmlspecialchars($course['title']); ?></h3>
                        <p style="margin:0; color:#6B7280;">
                            <strong>Instructor:</strong> <?php echo htmlspecialchars($course['instructor_name']); ?> |
                            <strong>Category:</strong> <?php echo htmlspecialchars($course['category_name']); ?> |
                            <strong>Price:</strong> $<?php echo number_format($course['price'],2); ?> |
                            <strong>Level:</strong> <span class="badge badge-info"><?php echo htmlspecialchars(ucfirst($course['level']??'N/A')); ?></span>
                        </p>
                    </div>
                    <div>
                        <span class="badge <?php echo $course['approval_status']==='approved'?'badge-success':($course['approval_status']==='pending'?'badge-warning':'badge-danger'); ?>">
                            <?php echo ucfirst($course['approval_status']); ?>
                        </span>
                    </div>
                </div>

                <p style="margin:0.5rem 0;"><?php echo htmlspecialchars(substr($course['description'],0,200)).'...'; ?></p>

                <?php if(!empty($course['rejection_reason'])): ?>
                    <div class="alert alert-danger">
                        <strong>Rejection Reason:</strong> <?php echo htmlspecialchars($course['rejection_reason']); ?>
                    </div>
                <?php endif; ?>

                <?php if($course['approval_status']==='pending'): ?>
                    <div class="course-actions">
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="approve">
                            <input type="hidden" name="course_id" value="<?php echo $course['course_id']; ?>">
                            <button type="submit" class="btn btn-success" onclick="return confirm('Approve this course?')">✓ Approve</button>
                        </form>
                        <button type="button" class="btn btn-danger" onclick="toggleRejectForm(<?php echo $course['course_id']; ?>)">✗ Reject</button>
                    </div>
                    <div id="reject-form-<?php echo $course['course_id']; ?>" class="rejection-form">
                        <form method="POST">
                            <input type="hidden" name="action" value="reject">
                            <input type="hidden" name="course_id" value="<?php echo $course['course_id']; ?>">
                            <div class="form-group">
                                <label>Rejection Reason</label>
                                <textarea name="rejection_reason" class="form-control" rows="3" required placeholder="Explain why this course is rejected..."></textarea>
                            </div>
                            <div style="display:flex; gap:0.5rem; margin-top:0.5rem;">
                                <button type="submit" class="btn btn-danger">Confirm Rejection</button>
                                <button type="button" class="btn btn-secondary" onclick="toggleRejectForm(<?php echo $course['course_id']; ?>)">Cancel</button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
function toggleRejectForm(courseId){
    const form = document.getElementById('reject-form-' + courseId);
    form.style.display = (form.style.display==='block') ? 'none' : 'block';
}
</script>
</body>
</html>
