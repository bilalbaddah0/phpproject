# TESTING GUIDE

## 🧪 Complete Testing Checklist

This guide will help you test all features of the E-Learning Management System.

## 🚀 Pre-Testing Setup

### 1. Verify Installation
- [ ] XAMPP Apache is running
- [ ] XAMPP MySQL is running
- [ ] Database `lms_database` exists
- [ ] All tables imported successfully
- [ ] Application loads at http://localhost/php/lms/

### 2. Verify Default Admin Account
- [ ] Can login with admin@lms.com / admin123
- [ ] Redirects to admin dashboard
- [ ] Dashboard shows statistics

---

## 👤 AUTHENTICATION TESTING

### Registration Tests
1. **Valid Student Registration**
   - Navigate to registration page
   - Fill form with valid data
   - Select "Student" role
   - Expected: Success message, redirect to login

2. **Valid Instructor Registration**
   - Fill form with different email
   - Select "Instructor" role
   - Expected: Registration successful

3. **Duplicate Email Test**
   - Try registering with existing email
   - Expected: Error message

4. **Password Mismatch Test**
   - Enter different passwords in password fields
   - Expected: Error message

5. **Short Password Test**
   - Try password less than 6 characters
   - Expected: Validation error

### Login Tests
1. **Valid Login**
   - Login with registered credentials
   - Expected: Redirect to role-specific dashboard

2. **Invalid Email**
   - Enter non-existent email
   - Expected: Error message

3. **Wrong Password**
   - Enter correct email, wrong password
   - Expected: Error message

4. **Empty Fields**
   - Try submitting empty form
   - Expected: Validation error

### Session Tests
1. **Session Persistence**
   - Login and navigate between pages
   - Expected: Stays logged in

2. **Logout**
   - Click logout button
   - Expected: Redirect to login, session cleared

3. **Direct URL Access (Not Logged In)**
   - Try accessing dashboard URLs directly
   - Expected: Redirect to login

---

## 👨‍🎓 STUDENT PORTAL TESTING

### Prerequisites
- Login as Student account

### Dashboard Tests
1. **View Dashboard**
   - Check statistics display correctly
   - Verify enrolled courses section
   - Expected: Shows 0 enrollments initially

### Course Browsing Tests
1. **Browse All Courses**
   - Navigate to "Browse Courses"
   - Expected: Shows published courses (if any)

2. **Search Functionality**
   - Enter search keyword
   - Click search
   - Expected: Filtered results

3. **Category Filter**
   - Select a category
   - Click search
   - Expected: Shows courses in category

4. **Combined Search**
   - Use search keyword + category
   - Expected: Filtered by both

### Enrollment Tests
1. **Enroll in Course**
   - Find a published course
   - Click "Enroll Now"
   - Expected: Success message, redirect to course

2. **Duplicate Enrollment**
   - Try enrolling in same course again
   - Expected: Already enrolled message

3. **View Enrolled Course**
   - Go to "My Learning"
   - Expected: Shows enrolled course with 0% progress

### Learning Experience Tests
1. **Course Overview**
   - Click on enrolled course
   - Expected: Shows course info and module list

2. **Select Lesson**
   - Click on a lesson in sidebar
   - Expected: Lesson content displays

3. **Video Content**
   - Test video lesson (if available)
   - Expected: Video player loads

4. **PDF Content**
   - Test PDF lesson (if available)
   - Expected: PDF displays in viewer

5. **Text Content**
   - Test text lesson
   - Expected: Text displays properly

6. **Complete Lesson**
   - Click "Mark as Complete"
   - Expected: Checkmark appears, progress updates

7. **Progress Tracking**
   - Complete multiple lessons
   - Check dashboard
   - Expected: Progress percentage increases

8. **Course Completion**
   - Complete all lessons
   - Expected: 100% progress, status changes to "Completed"

---

## 👨‍🏫 INSTRUCTOR PORTAL TESTING

### Prerequisites
- Login as Instructor account

### Dashboard Tests
1. **View Dashboard**
   - Check statistics (0 courses initially)
   - Verify course listing section
   - Expected: Shows empty state message

### Course Creation Tests
1. **Create Course - Basic Info**
   - Click "Create New Course"
   - Fill all required fields:
     * Title: "Test Web Development Course"
     * Description: "Learn web development basics"
     * Category: "Web Development"
     * Level: "Beginner"
     * Price: 0
   - Submit form
   - Expected: Course created, redirect to content management

2. **Validation Tests**
   - Try creating course with empty title
   - Expected: Validation error

3. **View Created Course**
   - Go to dashboard
   - Expected: Course appears in list with "Draft" status

### Content Management Tests
1. **Create Module**
   - Click "Add Module"
   - Enter module title: "Module 1: Introduction"
   - Add description (optional)
   - Submit
   - Expected: Module appears in list

2. **Create Text Lesson**
   - Click "Add Lesson" for a module
   - Enter lesson title: "Welcome to the Course"
   - Select content type: "Text Content"
   - Enter text content
   - Submit
   - Expected: Lesson appears under module

3. **Create Video Lesson**
   - Add another lesson
   - Select content type: "Video"
   - Enter YouTube URL: https://www.youtube.com/watch?v=dQw4w9WgXcQ
   - Submit
   - Expected: Lesson created with video icon

4. **Create PDF Lesson**
   - Add lesson
   - Select content type: "PDF"
   - Upload a PDF file
   - Submit
   - Expected: File uploaded, lesson created

5. **Create External Link Lesson**
   - Add lesson
   - Select content type: "External Link"
   - Enter URL
   - Submit
   - Expected: Lesson created with link icon

6. **Delete Lesson**
   - Click delete on a lesson
   - Confirm deletion
   - Expected: Lesson removed from list

7. **Module with Multiple Lessons**
   - Create 3-5 lessons in one module
   - Expected: All display in order

8. **Multiple Modules**
   - Create 2-3 modules
   - Add lessons to each
   - Expected: Organized structure

### Course Publishing Tests
1. **Publish Course**
   - Go to "Edit Course Details"
   - Change status to "Published"
   - Save
   - Expected: Status changes, course visible to students

2. **Archive Course**
   - Change status to "Archived"
   - Expected: Course hidden from students

### Course Editing Tests
1. **Edit Course Details**
   - Click "Edit" on a course
   - Modify title, description
   - Save changes
   - Expected: Changes reflected

2. **Change Category**
   - Edit course
   - Change category
   - Save
   - Expected: Updated successfully

3. **Update Price**
   - Change price value
   - Save
   - Expected: New price displayed

---

## 👨‍💼 ADMIN PORTAL TESTING

### Prerequisites
- Login as Admin (admin@lms.com / admin123)

### Dashboard Tests
1. **View Statistics**
   - Check total users count
   - Check total courses count
   - Expected: Accurate counts

2. **Recent Users Table**
   - Verify shows latest registered users
   - Expected: Correct data

3. **Recent Courses Table**
   - Verify shows latest courses
   - Expected: Correct data

### User Management Tests
1. **View All Users**
   - Navigate to "Users" page
   - Expected: Lists all users with details

2. **Filter by Role**
   - Click "Students" filter
   - Expected: Shows only students
   - Repeat for Instructors and Admins

3. **Change User Status**
   - Select a user
   - Change status dropdown (Active/Inactive/Suspended)
   - Expected: Status updates immediately

4. **Suspend User Test**
   - Suspend a test account
   - Logout admin
   - Try logging in as suspended user
   - Expected: Login fails with status message

5. **Delete User**
   - Click delete on test user
   - Confirm deletion
   - Expected: User removed from list

6. **Cannot Delete Self**
   - Try deleting admin account (yourself)
   - Expected: Error message

### Category Management Tests
1. **View Categories**
   - Navigate to "Categories"
   - Expected: Shows default categories

2. **Create Category**
   - Click "Add Category"
   - Enter name: "Test Category"
   - Add description
   - Submit
   - Expected: Category created

3. **Category Usage Count**
   - Check courses column
   - Expected: Shows number of courses

4. **Delete Empty Category**
   - Delete category with 0 courses
   - Expected: Deletion successful

5. **Cannot Delete Used Category**
   - Try deleting category with courses
   - Expected: Shows "In use" message

### Course Overview Tests
1. **View All Courses**
   - Navigate to "Courses"
   - Expected: Shows all courses (any status)

2. **Verify Course Data**
   - Check instructor names
   - Check enrollment counts
   - Check prices
   - Expected: Accurate information

---

## 🔄 INTEGRATION TESTING

### Complete Student Journey
1. Register as student
2. Browse courses
3. Enroll in 2 courses
4. Complete lessons in course 1
5. Reach 100% progress
6. Check dashboard statistics
7. Start course 2
8. Expected: Smooth flow, accurate tracking

### Complete Instructor Journey
1. Register as instructor
2. Create course with title and description
3. Add 2 modules
4. Add 3-4 lessons per module
5. Publish course
6. Wait for student enrollment (use student account)
7. Check dashboard for enrollment count
8. Expected: Course creation works, stats update

### Admin Oversight Journey
1. Login as admin
2. View new users (from above tests)
3. View new courses
4. Create new category
5. Monitor platform growth
6. Change user status
7. Expected: Full control and visibility

---

## 🎯 PROGRESS TRACKING TESTS

### Test Scenario 1: Partial Completion
1. Enroll in course with 10 lessons
2. Complete 5 lessons
3. Expected: 50% progress shown

### Test Scenario 2: Full Completion
1. Complete all remaining lessons
2. Expected: 100% progress, "Completed" badge

### Test Scenario 3: Multiple Courses
1. Enroll in 3 courses
2. Complete 1 fully
3. Partially complete 2
4. Expected: Dashboard shows accurate stats

---

## 🔒 SECURITY TESTING

### Access Control Tests
1. **Student Cannot Access Instructor Pages**
   - Login as student
   - Try URL: /views/instructor/dashboard.php
   - Expected: Redirect to student dashboard

2. **Instructor Cannot Access Admin Pages**
   - Login as instructor
   - Try URL: /views/admin/users.php
   - Expected: Redirect

3. **Cannot Edit Other's Courses**
   - Instructor A creates course
   - Instructor B tries to access edit URL
   - Expected: Redirect or error

### SQL Injection Tests
1. **Login Form**
   - Try: `admin@lms.com' OR '1'='1`
   - Expected: Login fails (protected)

2. **Search Fields**
   - Try SQL injection in search
   - Expected: No database errors

### XSS Tests
1. **Course Title**
   - Try: `<script>alert('XSS')</script>`
   - Expected: Displays as text, not executed

2. **Lesson Content**
   - Try JavaScript in text content
   - Expected: Escaped properly

---

## 📊 PERFORMANCE TESTS

### Load Tests
1. **Many Courses**
   - Create 20+ courses
   - Browse catalog
   - Expected: Loads reasonably fast

2. **Large Course**
   - Create course with 50+ lessons
   - Load course view
   - Expected: Handles well

3. **Multiple Enrollments**
   - Enroll in 10+ courses
   - Load dashboard
   - Expected: Displays all

---

## 🐛 ERROR HANDLING TESTS

### Database Errors
1. **Stop MySQL**
   - Stop MySQL in XAMPP
   - Try loading any page
   - Expected: Error message (not blank page)

2. **Invalid Course ID**
   - URL: /views/student/course_view.php?id=99999
   - Expected: Redirect or error message

### File Upload Errors
1. **Upload Large File**
   - Try uploading 100MB file
   - Expected: Size limit error

2. **Upload Wrong Type**
   - Try uploading .exe as PDF
   - Expected: Type validation error

3. **Upload Without Selection**
   - Submit lesson form without file
   - Expected: Validation message

---

## ✅ TESTING CHECKLIST SUMMARY

### Authentication (12 tests)
- [ ] Student registration
- [ ] Instructor registration
- [ ] Admin login
- [ ] Duplicate email prevention
- [ ] Password validation
- [ ] Login validation
- [ ] Session persistence
- [ ] Logout
- [ ] Access control

### Student Features (15 tests)
- [ ] Dashboard display
- [ ] Course browsing
- [ ] Search functionality
- [ ] Category filtering
- [ ] Course enrollment
- [ ] Lesson viewing (all types)
- [ ] Lesson completion
- [ ] Progress tracking
- [ ] Course completion

### Instructor Features (18 tests)
- [ ] Dashboard statistics
- [ ] Course creation
- [ ] Module creation
- [ ] Lesson creation (all types)
- [ ] Content organization
- [ ] Course editing
- [ ] Course publishing
- [ ] File uploads

### Admin Features (12 tests)
- [ ] User management
- [ ] Status updates
- [ ] User deletion
- [ ] Category management
- [ ] Course oversight
- [ ] Platform statistics

### Security (8 tests)
- [ ] Access control
- [ ] SQL injection protection
- [ ] XSS protection
- [ ] Session security

### Total Tests: 65+

---

## 📝 Bug Report Template

If you find issues during testing:

```
**Bug Title:** Brief description

**Steps to Reproduce:**
1. Step one
2. Step two
3. Step three

**Expected Result:**
What should happen

**Actual Result:**
What actually happened

**User Role:** Student/Instructor/Admin

**Browser:** Chrome/Firefox/Edge

**Screenshot:** (if applicable)
```

---

## 🎉 Testing Complete!

If all tests pass, your E-Learning Management System is ready for use!

**Final Checklist:**
- [ ] All authentication working
- [ ] Students can enroll and learn
- [ ] Instructors can create courses
- [ ] Admins can manage platform
- [ ] Progress tracking accurate
- [ ] Security measures working
- [ ] No major bugs found

**Congratulations! The system is production-ready! 🚀**
