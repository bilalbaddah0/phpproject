<?php
require_once __DIR__ . '/../config/database.php';

class Enrollment {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }

    // Enroll student in course
    public function enrollStudent($student_id, $course_id) {
        // Check if already enrolled
        if ($this->isEnrolled($student_id, $course_id)) {
            return ['success' => false, 'message' => 'Already enrolled in this course'];
        }

        $sql = "INSERT INTO enrollments (student_id, course_id) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $student_id, $course_id);

        if ($stmt->execute()) {
            return ['success' => true, 'enrollment_id' => $this->conn->insert_id];
        }
        return ['success' => false, 'message' => 'Enrollment failed'];
    }

    // Check if student is enrolled
    public function isEnrolled($student_id, $course_id) {
        $sql = "SELECT enrollment_id FROM enrollments WHERE student_id = ? AND course_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $student_id, $course_id);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    // Get enrollment by student and course
    public function getEnrollment($student_id, $course_id) {
        $sql = "SELECT * FROM enrollments WHERE student_id = ? AND course_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $student_id, $course_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Get student's enrolled courses
    public function getStudentCourses($student_id) {
        $sql = "SELECT c.*, e.enrollment_id, e.enrollment_date, e.progress_percentage, e.status as enrollment_status,
                cat.category_name, u.full_name as instructor_name
                FROM enrollments e
                JOIN courses c ON e.course_id = c.course_id
                LEFT JOIN categories cat ON c.category_id = cat.category_id
                LEFT JOIN users u ON c.instructor_id = u.user_id
                WHERE e.student_id = ?
                ORDER BY e.enrollment_date DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $courses = [];
        while ($row = $result->fetch_assoc()) {
            $courses[] = $row;
        }
        return $courses;
    }

    // Mark lesson as complete
    public function markLessonComplete($enrollment_id, $lesson_id) {
        // Check if progress exists
        $sql = "SELECT progress_id FROM lesson_progress WHERE enrollment_id = ? AND lesson_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $enrollment_id, $lesson_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Update existing progress
            $sql = "UPDATE lesson_progress SET is_completed = TRUE, completed_at = NOW() 
                    WHERE enrollment_id = ? AND lesson_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ii", $enrollment_id, $lesson_id);
        } else {
            // Insert new progress
            $sql = "INSERT INTO lesson_progress (enrollment_id, lesson_id, is_completed, completed_at) 
                    VALUES (?, ?, TRUE, NOW())";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ii", $enrollment_id, $lesson_id);
        }

        if ($stmt->execute()) {
            $this->updateCourseProgress($enrollment_id);
            return true;
        }
        return false;
    }

    // Update overall course progress
    private function updateCourseProgress($enrollment_id) {
        // Get total lessons in course
        $sql = "SELECT COUNT(*) as total FROM lessons l
                JOIN modules m ON l.module_id = m.module_id
                JOIN enrollments e ON m.course_id = e.course_id
                WHERE e.enrollment_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $enrollment_id);
        $stmt->execute();
        $total = $stmt->get_result()->fetch_assoc()['total'];

        // Get completed lessons
        $sql = "SELECT COUNT(*) as completed FROM lesson_progress 
                WHERE enrollment_id = ? AND is_completed = TRUE";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $enrollment_id);
        $stmt->execute();
        $completed = $stmt->get_result()->fetch_assoc()['completed'];

        // Calculate percentage
        $percentage = $total > 0 ? ($completed / $total) * 100 : 0;

        // Update enrollment
        $status = $percentage >= 100 ? 'completed' : 'active';
        $completion_date = $percentage >= 100 ? 'NOW()' : 'NULL';

        $sql = "UPDATE enrollments SET progress_percentage = ?, status = ?, 
                completion_date = " . ($percentage >= 100 ? 'NOW()' : 'NULL') . " 
                WHERE enrollment_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("dsi", $percentage, $status, $enrollment_id);
        $stmt->execute();
    }

    // Get lesson progress for enrollment
    public function getLessonProgress($enrollment_id) {
        $sql = "SELECT * FROM lesson_progress WHERE enrollment_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $enrollment_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $progress = [];
        while ($row = $result->fetch_assoc()) {
            $progress[$row['lesson_id']] = $row;
        }
        return $progress;
    }

    // Check if lesson is completed
    public function isLessonCompleted($enrollment_id, $lesson_id) {
        $sql = "SELECT is_completed FROM lesson_progress 
                WHERE enrollment_id = ? AND lesson_id = ? AND is_completed = TRUE";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $enrollment_id, $lesson_id);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    // Get course enrollments (for instructor)
    public function getCourseEnrollments($course_id) {
        $sql = "SELECT e.*, u.full_name, u.email 
                FROM enrollments e
                JOIN users u ON e.student_id = u.user_id
                WHERE e.course_id = ?
                ORDER BY e.enrollment_date DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $course_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $enrollments = [];
        while ($row = $result->fetch_assoc()) {
            $enrollments[] = $row;
        }
        return $enrollments;
    }
}
?>
