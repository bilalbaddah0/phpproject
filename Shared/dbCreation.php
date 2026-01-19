<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lms_database";

// Connect to MySQL server
$conn = new mysqli($servername, $username, $password);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully\n";
} else {
    echo "Error creating database: " . $conn->error . "\n";
}

$conn->select_db($dbname);

// Define table structures
$table1 = "CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    role ENUM('student','instructor','admin') NOT NULL,
    admin_status ENUM('pending','accepted','rejected') DEFAULT 'pending',
    instructor_status ENUM('pending','accepted','rejected') DEFAULT 'pending',
    joined_at DATETIME DEFAULT CURRENT_TIMESTAMP
)";

$table2 = "CREATE TABLE IF NOT EXISTS categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL,
    description TEXT
)";

$table3 = "CREATE TABLE IF NOT EXISTS courses (
    course_id INT AUTO_INCREMENT PRIMARY KEY,
    instructor_id INT NOT NULL,
    category_id INT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) DEFAULT 0.00,
    level ENUM('beginner','intermediate','advanced') DEFAULT 'beginner',
    approval_status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    approved_by INT NULL,
    approved_at DATETIME NULL,
    FOREIGN KEY (instructor_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE SET NULL
)";

$table4 = "CREATE TABLE IF NOT EXISTS enrollments (
    enrollment_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    FOREIGN KEY (student_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE
)";

$table5 = "CREATE TABLE IF NOT EXISTS student_course_status (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    is_completed TINYINT(1) DEFAULT 0,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE (student_id, course_id),
    FOREIGN KEY (student_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE
)";

// Create all tables
$tables = [$table1, $table2, $table3, $table4, $table5];
$tableNames = ['Users', 'Categories', 'Courses', 'Enrollments', 'Student Course Status'];

foreach ($tables as $index => $sql) {
    if ($conn->query($sql) === TRUE) {
        echo $tableNames[$index] . " table created successfully\n";
    } else {
        echo "Error creating " . strtolower($tableNames[$index]) . " table: " . $conn->error . "\n";
    }
}

$conn->close();
?>
