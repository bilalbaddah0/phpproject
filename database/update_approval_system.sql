-- Add approval_status and admin approval features to courses table
ALTER TABLE courses 
ADD COLUMN approval_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending' AFTER status,
ADD COLUMN approved_by INT NULL AFTER approval_status,
ADD COLUMN approved_at DATETIME NULL AFTER approved_by,
ADD COLUMN rejection_reason TEXT NULL AFTER approved_at;

-- Add password reset tokens table for forgot password functionality
CREATE TABLE IF NOT EXISTS password_resets (
    reset_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    token VARCHAR(100) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    used BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Update existing courses to approved status (for existing data)
UPDATE courses SET approval_status = 'approved' WHERE status = 'published';
