# PHP Project

## How to Run the Project

1. Install **XAMPP** or **WAMP**
2. Move the project folder to:

```
htdocs/
```

3. Start **Apache** and **MySQL**
4. Open the `Shared/DbConnection.php` file and run it:
5. Import the SQL file into your database:

   * Open **phpMyAdmin**
   * Open the database`lms_database`
   * Click **Import** and select the SQL file from the main project folder
6. Open browser and visit:

```
http://localhost/project-folder-name
```

---

## 👤 Default Users (Example)

| Role       | Email                                           | Password      |
| ---------- | ----------------------------------------------- | ------------- |
| Admin      | [admin@lms.com](mailto:admin@lms.com)           | admin123      |
| Student    | [student@lms.com](mailto:student@lms.com)       | student123    |
| Instructor | [instructor@lms.com](mailto:instructor@lms.com) | instructor123 |

> Passwords are stored hashed in the database.
