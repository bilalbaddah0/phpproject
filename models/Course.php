<?php
require_once __DIR__ . '/../config/database.php';

class Course {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }

    // Create new course
    public function createCourse($instructor_id, $title, $description, $category_id, $price = 0, $level = 'beginner') {
        $sql = "INSERT INTO courses (instructor_id, title, description, category_id, price, level, status, approval_status) 
                VALUES (?, ?, ?, ?, ?, ?, 'draft', 'pending')";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("issids", $instructor_id, $title, $description, $category_id, $price, $level);

        if ($stmt->execute()) {
            return ['success' => true, 'course_id' => $this->conn->insert_id];
        }
        return ['success' => false];
    }

    // Update course
    public function updateCourse($course_id, $title, $description, $category_id, $price, $level, $status = null) {
        if ($status) {
            $sql = "UPDATE courses SET title = ?, description = ?, category_id = ?, price = ?, level = ?, status = ? 
                    WHERE course_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ssidssi", $title, $description, $category_id, $price, $level, $status, $course_id);
        } else {
            $sql = "UPDATE courses SET title = ?, description = ?, category_id = ?, price = ?, level = ? 
                    WHERE course_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ssidsi", $title, $description, $category_id, $price, $level, $course_id);
        }

        return $stmt->execute();
    }

    // Get course by ID
    public function getCourseById($course_id) {
        $sql = "SELECT c.*, cat.category_name, u.full_name as instructor_name 
                FROM courses c 
                LEFT JOIN categories cat ON c.category_id = cat.category_id 
                LEFT JOIN users u ON c.instructor_id = u.user_id 
                WHERE c.course_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $course_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Get all published courses
    public function getAllPublishedCourses($search = null, $category_id = null) {
        $sql = "SELECT c.*, cat.category_name, u.full_name as instructor_name,
                (SELECT COUNT(*) FROM enrollments WHERE course_id = c.course_id) as enrollment_count
                FROM courses c 
                LEFT JOIN categories cat ON c.category_id = cat.category_id 
                LEFT JOIN users u ON c.instructor_id = u.user_id 
                WHERE c.status = 'published' AND c.approval_status = 'approved'";

        if ($search) {
            $sql .= " AND (c.title LIKE ? OR c.description LIKE ?)";
        }

        if ($category_id) {
            $sql .= " AND c.category_id = ?";
        }

        $sql .= " ORDER BY c.created_at DESC";

        $stmt = $this->conn->prepare($sql);

        if ($search && $category_id) {
            $search_term = "%$search%";
            $stmt->bind_param("ssi", $search_term, $search_term, $category_id);
        } elseif ($search) {
            $search_term = "%$search%";
            $stmt->bind_param("ss", $search_term, $search_term);
        } elseif ($category_id) {
            $stmt->bind_param("i", $category_id);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $courses = [];
        while ($row = $result->fetch_assoc()) {
            $courses[] = $row;
        }
        return $courses;
    }

    // Get courses by instructor
    public function getCoursesByInstructor($instructor_id) {
        $sql = "SELECT c.*, cat.category_name,
                (SELECT COUNT(*) FROM enrollments WHERE course_id = c.course_id) as enrollment_count
                FROM courses c 
                LEFT JOIN categories cat ON c.category_id = cat.category_id 
                WHERE c.instructor_id = ?
                ORDER BY c.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $instructor_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $courses = [];
        while ($row = $result->fetch_assoc()) {
            $courses[] = $row;
        }
        return $courses;
    }

    // Delete course
    public function deleteCourse($course_id, $instructor_id = null) {
        if ($instructor_id) {
            $sql = "DELETE FROM courses WHERE course_id = ? AND instructor_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ii", $course_id, $instructor_id);
        } else {
            $sql = "DELETE FROM courses WHERE course_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $course_id);
        }
        return $stmt->execute();
    }

    // Get course modules
    public function getCourseModules($course_id) {
        $sql = "SELECT * FROM modules WHERE course_id = ? ORDER BY module_order ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $course_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $modules = [];
        while ($row = $result->fetch_assoc()) {
            $modules[] = $row;
        }
        return $modules;
    }

    // Add module to course
    public function addModule($course_id, $module_title, $description = null) {
        // Get next order number
        $sql = "SELECT COALESCE(MAX(module_order), 0) + 1 as next_order FROM modules WHERE course_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $course_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $order = $result['next_order'];

        $sql = "INSERT INTO modules (course_id, module_title, module_order, description) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isis", $course_id, $module_title, $order, $description);

        if ($stmt->execute()) {
            return ['success' => true, 'module_id' => $this->conn->insert_id];
        }
        return ['success' => false];
    }

    // Get module lessons
    public function getModuleLessons($module_id) {
        $sql = "SELECT * FROM lessons WHERE module_id = ? ORDER BY lesson_order ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $module_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $lessons = [];
        while ($row = $result->fetch_assoc()) {
            $lessons[] = $row;
        }
        return $lessons;
    }

    // Add lesson to module
    public function addLesson($module_id, $lesson_title, $content_type, $content_text = null, $content_url = null, $file_path = null) {
        // Get next order number
        $sql = "SELECT COALESCE(MAX(lesson_order), 0) + 1 as next_order FROM lessons WHERE module_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $module_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $order = $result['next_order'];

        $sql = "INSERT INTO lessons (module_id, lesson_title, lesson_order, content_type, content_text, content_url, file_path) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isissss", $module_id, $lesson_title, $order, $content_type, $content_text, $content_url, $file_path);

        if ($stmt->execute()) {
            return ['success' => true, 'lesson_id' => $this->conn->insert_id];
        }
        return ['success' => false];
    }

    // Get lesson by ID
    public function getLessonById($lesson_id) {
        $sql = "SELECT l.*, m.course_id FROM lessons l 
                JOIN modules m ON l.module_id = m.module_id 
                WHERE l.lesson_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $lesson_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Update lesson
    public function updateLesson($lesson_id, $lesson_title, $content_type, $content_text = null, $content_url = null, $file_path = null) {
        $sql = "UPDATE lessons SET lesson_title = ?, content_type = ?, content_text = ?, content_url = ?, file_path = ? 
                WHERE lesson_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssssi", $lesson_title, $content_type, $content_text, $content_url, $file_path, $lesson_id);
        return $stmt->execute();
    }

    // Delete lesson
    public function deleteLesson($lesson_id) {
        $sql = "DELETE FROM lessons WHERE lesson_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $lesson_id);
        return $stmt->execute();
    }

    // Get all categories
    public function getAllCategories() {
        $sql = "SELECT * FROM categories ORDER BY category_name ASC";
        $result = $this->conn->query($sql);

        $categories = [];
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
        return $categories;
    }

    // Get course statistics
    public function getCourseStats($course_id) {
        $stats = [];

        // Total enrollments
        $sql = "SELECT COUNT(*) as total FROM enrollments WHERE course_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $course_id);
        $stmt->execute();
        $stats['enrollments'] = $stmt->get_result()->fetch_assoc()['total'];

        // Completed enrollments
        $sql = "SELECT COUNT(*) as total FROM enrollments WHERE course_id = ? AND status = 'completed'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $course_id);
        $stmt->execute();
        $stats['completed'] = $stmt->get_result()->fetch_assoc()['total'];

        // Total lessons
        $sql = "SELECT COUNT(*) as total FROM lessons l 
                JOIN modules m ON l.module_id = m.module_id 
                WHERE m.course_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $course_id);
        $stmt->execute();
        $stats['lessons'] = $stmt->get_result()->fetch_assoc()['total'];

        return $stats;
    }
}
?>
