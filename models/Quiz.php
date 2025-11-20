<?php
require_once __DIR__ . '/../config/database.php';

class Quiz {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }

    // Create quiz
    public function createQuiz($course_id, $module_id, $quiz_title, $description, $passing_score = 70, $time_limit = null) {
        $sql = "INSERT INTO quizzes (course_id, module_id, quiz_title, description, passing_score, time_limit) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iissii", $course_id, $module_id, $quiz_title, $description, $passing_score, $time_limit);

        if ($stmt->execute()) {
            return ['success' => true, 'quiz_id' => $this->conn->insert_id];
        }
        return ['success' => false];
    }

    // Add question to quiz
    public function addQuestion($quiz_id, $question_text, $question_type, $points = 1) {
        // Get next order
        $sql = "SELECT COALESCE(MAX(question_order), 0) + 1 as next_order FROM quiz_questions WHERE quiz_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $quiz_id);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc()['next_order'];

        $sql = "INSERT INTO quiz_questions (quiz_id, question_text, question_type, points, question_order) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("issii", $quiz_id, $question_text, $question_type, $points, $order);

        if ($stmt->execute()) {
            return ['success' => true, 'question_id' => $this->conn->insert_id];
        }
        return ['success' => false];
    }

    // Add answer option
    public function addOption($question_id, $option_text, $is_correct, $option_order) {
        $sql = "INSERT INTO quiz_options (question_id, option_text, is_correct, option_order) 
                VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isii", $question_id, $option_text, $is_correct, $option_order);
        return $stmt->execute();
    }

    // Get quiz by ID
    public function getQuizById($quiz_id) {
        $sql = "SELECT * FROM quizzes WHERE quiz_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $quiz_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Get quiz questions with options
    public function getQuizQuestions($quiz_id) {
        $sql = "SELECT * FROM quiz_questions WHERE quiz_id = ? ORDER BY question_order ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $quiz_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $questions = [];
        while ($row = $result->fetch_assoc()) {
            $question_id = $row['question_id'];
            $row['options'] = $this->getQuestionOptions($question_id);
            $questions[] = $row;
        }
        return $questions;
    }

    // Get question options
    public function getQuestionOptions($question_id) {
        $sql = "SELECT * FROM quiz_options WHERE question_id = ? ORDER BY option_order ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $question_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $options = [];
        while ($row = $result->fetch_assoc()) {
            $options[] = $row;
        }
        return $options;
    }

    // Start quiz attempt
    public function startAttempt($enrollment_id, $quiz_id) {
        $sql = "INSERT INTO quiz_attempts (enrollment_id, quiz_id) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $enrollment_id, $quiz_id);

        if ($stmt->execute()) {
            return ['success' => true, 'attempt_id' => $this->conn->insert_id];
        }
        return ['success' => false];
    }

    // Submit answer
    public function submitAnswer($attempt_id, $question_id, $option_id) {
        // Check if answer is correct
        $sql = "SELECT is_correct FROM quiz_options WHERE option_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $option_id);
        $stmt->execute();
        $is_correct = $stmt->get_result()->fetch_assoc()['is_correct'];

        $sql = "INSERT INTO quiz_answers (attempt_id, question_id, option_id, is_correct) 
                VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iiii", $attempt_id, $question_id, $option_id, $is_correct);
        return $stmt->execute();
    }

    // Complete quiz attempt
    public function completeAttempt($attempt_id) {
        // Calculate score
        $sql = "SELECT 
                    SUM(CASE WHEN qa.is_correct = 1 THEN qq.points ELSE 0 END) as earned_points,
                    SUM(qq.points) as total_points
                FROM quiz_answers qa
                JOIN quiz_questions qq ON qa.question_id = qq.question_id
                WHERE qa.attempt_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $attempt_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        $earned = $result['earned_points'] ?? 0;
        $total = $result['total_points'] ?? 1;
        $score = ($earned / $total) * 100;

        // Get passing score
        $sql = "SELECT q.passing_score FROM quiz_attempts qa 
                JOIN quizzes q ON qa.quiz_id = q.quiz_id 
                WHERE qa.attempt_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $attempt_id);
        $stmt->execute();
        $passing_score = $stmt->get_result()->fetch_assoc()['passing_score'];

        $passed = $score >= $passing_score;

        // Update attempt
        $sql = "UPDATE quiz_attempts SET score = ?, total_points = ?, passed = ?, completed_at = NOW() 
                WHERE attempt_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("diii", $score, $total, $passed, $attempt_id);
        
        if ($stmt->execute()) {
            return ['success' => true, 'score' => $score, 'passed' => $passed];
        }
        return ['success' => false];
    }

    // Get student's quiz attempts
    public function getStudentAttempts($enrollment_id, $quiz_id) {
        $sql = "SELECT * FROM quiz_attempts 
                WHERE enrollment_id = ? AND quiz_id = ? 
                ORDER BY started_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $enrollment_id, $quiz_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $attempts = [];
        while ($row = $result->fetch_assoc()) {
            $attempts[] = $row;
        }
        return $attempts;
    }

    // Get course quizzes
    public function getCourseQuizzes($course_id) {
        $sql = "SELECT q.*, m.module_title 
                FROM quizzes q
                LEFT JOIN modules m ON q.module_id = m.module_id
                WHERE q.course_id = ?
                ORDER BY q.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $course_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $quizzes = [];
        while ($row = $result->fetch_assoc()) {
            $quizzes[] = $row;
        }
        return $quizzes;
    }

    // Get quiz statistics
    public function getQuizStats($quiz_id) {
        $stats = [];

        // Total attempts
        $sql = "SELECT COUNT(*) as total FROM quiz_attempts WHERE quiz_id = ? AND completed_at IS NOT NULL";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $quiz_id);
        $stmt->execute();
        $stats['total_attempts'] = $stmt->get_result()->fetch_assoc()['total'];

        // Average score
        $sql = "SELECT AVG(score) as avg_score FROM quiz_attempts WHERE quiz_id = ? AND completed_at IS NOT NULL";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $quiz_id);
        $stmt->execute();
        $stats['average_score'] = round($stmt->get_result()->fetch_assoc()['avg_score'] ?? 0, 2);

        // Pass rate
        $sql = "SELECT COUNT(*) as passed FROM quiz_attempts WHERE quiz_id = ? AND passed = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $quiz_id);
        $stmt->execute();
        $passed = $stmt->get_result()->fetch_assoc()['passed'];
        $stats['pass_rate'] = $stats['total_attempts'] > 0 ? round(($passed / $stats['total_attempts']) * 100, 2) : 0;

        return $stats;
    }
}
?>
