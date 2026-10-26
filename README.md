# Student Management System

A PHP and MySQL student management application developed for **IT2133 - Advanced Web Programming** at the **University of Vavuniya, Sri Lanka**.

The application lets an administrator sign in, view a dashboard, and register, view, edit, and delete student records.

## Project Status

The core authentication and CRUD features have been implemented. The remaining assignment features are listed below.

| Feature | Status |
| --- | --- |
| Database schema and project structure | Implemented |
| Dashboard with total student count | Implemented |
| Administrator login, sessions, and logout | Implemented |
| Student registration and record listing | Implemented |
| Student editing and updating | Implemented |
| Student deletion with a confirmation page | Implemented |
| Prepared statements for user-supplied SQL values | Implemented |
| Search by Student ID, name, or email | Pending |
| Department filtering | Pending |
| Sorting by name and academic year, ascending or descending | Pending |
| Pagination with 10 records per page | Pending |

Search, filtering, and sorting must work together. Pagination must preserve those selections and provide Previous, Next, and page-number navigation.

## Technology Stack

| Layer | Technology |
| --- | --- |
| Interface | HTML and CSS |
| Browser behaviour | JavaScript |
| Backend | PHP |
| Database access | MySQLi |
| Database | MySQL-compatible database |
| Local server | XAMPP: Apache and MySQL service |
| Development environment | Windows 11 and VS Code |

JavaScript disables the confirmation button while a deletion request is being submitted. Authentication, validation, and database operations run on the server.

## Current Features

- Administrator login using an email address and password.
- Session-based protection for application pages.
- Dashboard displaying the current number of students.
- Student registration with server-side validation.
- Student listing with Edit and Delete actions.
- Editing of existing student details.
- A deletion confirmation page showing the selected student's details.
- Logout through a POST form.
- Success messages after creating, updating, or deleting a record.
- Responsive layouts and horizontally scrollable student tables.

The current registration and edit forms offer **IT** and **Physical Science** as department options, and **Year 1-4** as academic-year options.

## Project Files

| Path | Purpose |
| --- | --- |
| `index.php` | Routes visitors to the dashboard or login page |
| `login.php` | Validates administrator credentials |
| `logout.php` | Clears the authenticated session |
| `dashboard.php` | Displays the dashboard and student count |
| `students.php` | Displays student records and action links |
| `add_student.php` | Validates and inserts a student |
| `edit_student.php` | Loads and updates an existing student |
| `delete_student.php` | Confirms and processes student deletion |
| `create_admin.php` | Creates the initial administrator through the terminal |
| `config/database.php` | Configures the MySQLi connection |
| `includes/auth.php` | Starts the session, provides a CSRF token, and checks login status |
| `assets/style.css` | Styles the application |
| `database.sql` | Creates the database and tables |
| `README.md` | Documents setup, usage, and project progress |

## Requirements

- Windows with XAMPP installed.
- PHP 8.x with the `mysqli` and `mbstring` extensions enabled.
- Apache and the MySQL service running.
- A web browser.
- VS Code or another text editor.

## Local Setup

### 1. Place the project inside htdocs

Download or clone this repository into XAMPP's `htdocs` directory.

The examples in this README use:

```text
C:\xampp\htdocs\PHP Projects\Student-management
```

Open that folder in VS Code. If you choose another folder, adjust the terminal paths and browser URLs accordingly.

### 2. Start XAMPP

Open the XAMPP Control Panel and start:

- **Apache**
- **MySQL**

Open [phpMyAdmin](http://localhost/phpmyadmin/).

### 3. Import the database schema

Use phpMyAdmin's **Import** tab to import `database.sql`.

The schema creates the database named:

```text
student-management
```

It contains two tables: `users` and `students`. The schema uses `utf8mb4` to support Unicode text, including Sinhala.

### 4. Check the database configuration

Open `config/database.php` and match these settings to your installation:

```php
$dbHost = 'localhost';
$dbUser = 'root';
$dbPassword = '';
$dbName = 'student-management';
$dbPort = 3306;
```

These are the local XAMPP settings used during development.

### 5. Create the initial administrator

In a PowerShell terminal, run:

```powershell
& "C:\xampp\php\php.exe" "C:\xampp\htdocs\PHP Projects\Student-management\create_admin.php"
```

Enter the administrator's name, email, password, and password confirmation when prompted.

- There is **no default administrator email or password**.
- The setup script accepts passwords between 12 and 72 bytes.
- The password is stored as a hash.
- The script runs only through the terminal.
- It stops if a record already exists in the `users` table.

Password input is visible in the terminal, so exclude that input from screenshots.

### 6. Open the application

Visit:

[http://localhost/PHP%20Projects/Student-management/](http://localhost/PHP%20Projects/Student-management/)

Sign in with the credentials you chose during administrator setup.

## Database Design

### users

| Field | Type | Constraint / purpose |
| --- | --- | --- |
| `id` | INT | Auto-increment primary key |
| `name` | VARCHAR(100) | Required administrator name |
| `email` | VARCHAR(150) | Required, unique email |
| `password` | VARCHAR(255) | Required password hash |

### students

| Field | Type | Constraint / purpose |
| --- | --- | --- |
| `id` | INT | Auto-increment primary key |
| `student_id` | VARCHAR(20) | Required, unique student identifier |
| `student_name` | VARCHAR(100) | Required student name |
| `email` | VARCHAR(150) | Required, unique email |
| `department` | VARCHAR(100) | Required department |
| `academic_year` | INT | Required study year |

The numeric `id` identifies a database record internally. The separate `student_id` field holds the student's university identifier.

## Using the Application

1. Sign in through the administrator login page.
2. View the student count on the dashboard.
3. Choose **Add Student**, enter the details, and select **Register Student**.
4. Open **Manage Students** to view registered records.
5. Select **Edit**, change the details, and save.
6. Select **Delete**, review the confirmation page, and either confirm or cancel.
7. Select **Logout** to end the authenticated session.

## Security Practices Used

- `password_hash()` stores password hashes; `password_verify()` checks login passwords.
- The session ID is regenerated after successful login.
- Protected pages call `requireLogin()`.
- Queries that use user-supplied values use MySQLi prepared statements.
- Forms use session-based CSRF tokens.
- Output is escaped with `htmlspecialchars()`.
- Server-side validation checks required fields, lengths, email format, departments, and academic years.
- Unique constraints prevent duplicate Student IDs and student emails.
- Student deletion happens through a validated POST request after confirmation.

## Manual Checks

After setup, verify that:

- Valid credentials allow login and invalid credentials are rejected.
- Opening a protected page while signed out redirects to login.
- A new student appears in the records table and increases the dashboard count.
- Duplicate Student IDs and emails are rejected.
- Editing a student updates the selected record.
- Cancelling deletion preserves the record.
- Confirming deletion removes only the selected record and updates the count.
- A table with no students displays its empty-state message.
- Sinhala text and names containing apostrophes display correctly.
- Refreshing the records page does not repeat an insert operation.
- Logout prevents further access to protected pages until the next login.

## Troubleshooting

| Problem | Check |
| --- | --- |
| Database connection failed | Confirm MySQL is running, the schema is imported, and the credentials and port match `config/database.php` |
| MySQL does not start | Inspect the XAMPP MySQL error log and check whether its configured port is available |
| Could not open input file | Confirm the complete `create_admin.php` path and keep paths containing spaces inside quotes |
| Styles are missing | Check `assets/style.css` and refresh with Ctrl + F5 |
| Student records do not appear | Check the active database in phpMyAdmin and confirm `students.php` renders the fetched student array |
| Form expired or invalid request | Reload the page to obtain the current session's form token |

## Author

**Induwara Imesh Senavirathna**  
BSc (Hons) IT Undergraduate  
University of Vavuniya, Sri Lanka

Developed as an educational project for **IT2133 - Advanced Web Programming**.

