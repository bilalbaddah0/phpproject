# New Features Added - November 20, 2025

## 1. Course Approval System ✅

### Overview
All courses created by instructors now require admin approval before they can be published and visible to students.

### How It Works

#### For Instructors:
- When you create a course, it starts with status **"Pending"**
- You can see the approval status on your dashboard in the "Approval" column
- Approval statuses:
  - **Pending** (Yellow) - Waiting for admin approval
  - **Approved** (Green) - Course can be published
  - **Rejected** (Red) - Course needs changes (hover over ℹ️ to see reason)

#### For Admins:
1. Go to **Admin Dashboard** → **Approvals** menu
2. View courses in different tabs:
   - Pending - Courses waiting for approval
   - Approved - All approved courses
   - Rejected - Courses that were rejected
   - All Courses - Complete list
3. For each pending course:
   - Click **"✓ Approve"** to approve
   - Click **"✗ Reject"** to reject (you must provide a reason)

### Database Changes
- Added `approval_status` column to courses table
- Added `approved_by`, `approved_at`, `rejection_reason` columns
- Existing published courses automatically set to "approved"

---

## 2. Forgot Password Feature ✅

### Overview
Users who forget their password can now request a password reset link.

### How It Works

#### For Users:
1. On the login page, click **"Forgot Password?"** link
2. Enter your email address
3. A password reset link will be generated
4. **DEMO MODE**: In production, this link would be emailed. For now:
   - After submitting, you'll see a success message
   - The reset link is logged to the PHP error log
   - Or use the direct link format: `http://localhost:8080/php/lms/views/auth/reset_password.php?token=YOUR_TOKEN`

#### Password Reset Process:
1. Click the reset link (valid for 1 hour)
2. Enter your new password (minimum 6 characters)
3. Confirm the password
4. Submit - you'll be redirected to login with success message

### Security Features:
- Tokens are unique and expire after 1 hour
- Tokens can only be used once
- Passwords are re-hashed using bcrypt
- No email enumeration (always shows success even if email doesn't exist)

### Database Changes
- New `password_resets` table created with:
  - reset_id, user_id, token, expires_at, created_at, used

---

## 3. Course Visibility Fix ✅

### The Problem
Courses created by instructors were not showing up for students to browse.

### The Solution
Updated the course listing query to only show courses that are:
1. **Status**: Published (not draft or archived)
2. **Approval**: Approved by admin

This means instructors must:
1. Create course
2. Wait for admin approval
3. Publish the course
4. Then it becomes visible to students

---

## Files Created/Modified

### New Files:
1. `views/auth/forgot_password.php` - Forgot password form
2. `views/auth/reset_password.php` - Reset password form
3. `controllers/PasswordController.php` - Handles password reset logic
4. `views/admin/course_approvals.php` - Admin approval interface
5. `database/update_approval_system.sql` - SQL migration script
6. `update_database.php` - Database update script

### Modified Files:
1. `index.php` - Added "Forgot Password?" link
2. `models/Course.php` - Updated to handle approval_status
3. `views/instructor/dashboard.php` - Added approval status column
4. `views/admin/dashboard.php` - Added "Approvals" menu link

---

## How to Use the New Features

### Step 1: Update Database
Run: `http://localhost:8080/php/lms/update_database.php`

This will:
- Add approval columns to courses table
- Create password_resets table
- Set existing published courses to "approved"

### Step 2: Test Forgot Password
1. Go to login page
2. Click "Forgot Password?"
3. Enter: `admin@lms.com`
4. Submit
5. Check PHP error log or create a demo page to show the reset link

### Step 3: Test Course Approval
1. **As Instructor**:
   - Login as instructor
   - Create a new course
   - Notice it has "Pending" approval status
   - Publish button might be disabled until approved

2. **As Admin**:
   - Login as admin (admin@lms.com / admin123)
   - Go to "Approvals" menu
   - See the pending course
   - Approve or reject it

3. **As Student**:
   - Browse courses
   - Only see approved + published courses

---

## Additional Features to Consider

Would you like me to add:

1. **Email Integration** - Actually send reset emails using PHPMailer
2. **Course Comments/Reviews** - Let students review courses
3. **Progress Tracking** - Track which lessons students have completed
4. **Certificates** - Generate completion certificates
5. **Notifications** - Notify instructors when courses are approved/rejected
6. **Dashboard Analytics** - Charts and graphs for admin
7. **Search & Filters** - Advanced course search
8. **File Uploads** - Upload course materials (PDFs, videos)
9. **Quiz Timer** - Add countdown timer to quizzes
10. **Discussion Forums** - Course-specific forums

Let me know what you'd like to add next!
