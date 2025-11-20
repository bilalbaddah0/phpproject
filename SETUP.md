# QUICK SETUP GUIDE

## 🚀 Get Started in 5 Minutes

### 1. Start XAMPP
- Open XAMPP Control Panel
- Click "Start" for Apache
- Click "Start" for MySQL

### 2. Import Database
- Open browser: http://localhost/phpmyadmin
- Click "New" to create database or use Import
- Select "Import" tab
- Choose file: `lms/database/schema.sql`
- Click "Go"

### 3. Access Application
- Open browser: http://localhost/php/lms/
- Login with: admin@lms.com / admin123

### 4. Create Test Accounts
- Click "Register here" 
- Create Instructor account
- Create Student account

### 5. Test the System

**As Instructor:**
1. Login with instructor account
2. Create a new course
3. Add modules and lessons
4. Publish the course

**As Student:**
1. Login with student account
2. Browse courses
3. Enroll in a course
4. Complete lessons

**As Admin:**
1. Login with admin account
2. View all users
3. Manage categories
4. Monitor platform statistics

## ✅ Verification Checklist

- [ ] XAMPP Apache running (port 80)
- [ ] XAMPP MySQL running (port 3306)
- [ ] Database `lms_database` created
- [ ] Login page loads successfully
- [ ] Can login with admin account
- [ ] Can register new accounts

## 🐛 Common Issues

**Database Connection Failed**
- Make sure MySQL is running in XAMPP
- Database name is `lms_database`
- Username: `root`, Password: (empty)

**Page Not Found**
- Check BASE_URL in `config/config.php`
- Should be: `http://localhost/php/lms/`

**Can't Upload Files**
- Right-click `assets/uploads` folder
- Properties → Security → Give full control

## 📱 Test User Accounts

After setup, you'll have:
- 1 Admin account (pre-created)
- Register your own Instructor account
- Register your own Student account

## 🎯 Quick Feature Test

1. **Student Journey:**
   - Register → Browse → Enroll → Learn → Track Progress

2. **Instructor Journey:**
   - Register → Create Course → Add Content → Publish

3. **Admin Journey:**
   - Login → Manage Users → Create Categories → Monitor

---

Need help? Check README.md for detailed documentation!
