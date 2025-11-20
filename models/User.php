<?php
require_once __DIR__ . '/../config/database.php';

class User {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }

    // Register new user
    public function register($email, $password, $full_name, $role = 'student') {
        // Check if email already exists
        if ($this->emailExists($email)) {
            return ['success' => false, 'message' => 'Email already registered'];
        }

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Prepare statement
        $sql = "INSERT INTO users (email, password, full_name, role) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssss", $email, $hashed_password, $full_name, $role);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Registration successful', 'user_id' => $this->conn->insert_id];
        } else {
            return ['success' => false, 'message' => 'Registration failed'];
        }
    }

    // Login user
    public function login($email, $password) {
        $sql = "SELECT user_id, email, password, full_name, role, status FROM users WHERE email = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Check if account is active
            if ($user['status'] !== 'active') {
                return ['success' => false, 'message' => 'Account is ' . $user['status']];
            }

            // Verify password
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];

                return ['success' => true, 'user' => $user];
            } else {
                return ['success' => false, 'message' => 'Invalid password'];
            }
        } else {
            return ['success' => false, 'message' => 'Email not found'];
        }
    }

    // Check if email exists
    public function emailExists($email) {
        $sql = "SELECT user_id FROM users WHERE email = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    // Get user by ID
    public function getUserById($user_id) {
        $sql = "SELECT user_id, email, full_name, role, profile_picture, bio, status, created_at FROM users WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // Update user profile
    public function updateProfile($user_id, $full_name, $bio = null, $profile_picture = null) {
        if ($profile_picture) {
            $sql = "UPDATE users SET full_name = ?, bio = ?, profile_picture = ? WHERE user_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("sssi", $full_name, $bio, $profile_picture, $user_id);
        } else {
            $sql = "UPDATE users SET full_name = ?, bio = ? WHERE user_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ssi", $full_name, $bio, $user_id);
        }

        return $stmt->execute();
    }

    // Change password
    public function changePassword($user_id, $old_password, $new_password) {
        // Get current password
        $sql = "SELECT password FROM users WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        // Verify old password
        if (!password_verify($old_password, $user['password'])) {
            return ['success' => false, 'message' => 'Current password is incorrect'];
        }

        // Update password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET password = ? WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $hashed_password, $user_id);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Password changed successfully'];
        } else {
            return ['success' => false, 'message' => 'Password change failed'];
        }
    }

    // Get all users (for admin)
    public function getAllUsers($role = null) {
        if ($role) {
            $sql = "SELECT user_id, email, full_name, role, status, created_at FROM users WHERE role = ? ORDER BY created_at DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("s", $role);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $sql = "SELECT user_id, email, full_name, role, status, created_at FROM users ORDER BY created_at DESC";
            $result = $this->conn->query($sql);
        }

        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        return $users;
    }

    // Update user status (for admin)
    public function updateUserStatus($user_id, $status) {
        $sql = "UPDATE users SET status = ? WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $status, $user_id);
        return $stmt->execute();
    }

    // Delete user (for admin)
    public function deleteUser($user_id) {
        $sql = "DELETE FROM users WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        return $stmt->execute();
    }

    // Logout
    public static function logout() {
        session_unset();
        session_destroy();
    }
}
?>
