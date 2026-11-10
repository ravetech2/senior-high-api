# Senior High School Management System API (MySQLi)

This API is based on the uploaded `database.sql` schema. It uses **MySQLi**, not PDO, and separates every API module into its own route file.

## Database tables
- `admin` – administrator login accounts
- `year_levels` – Grade 11 and Grade 12
- `strands` – STEM, ABM, HUMSS, GAS, TVL-ICT
- `students` – student profile and login information
- `subjects` – subjects assigned to year level and optional strand
- `student_subjects` – subject enrollment by school year and semester
- `grades` – quarterly grades linked to an enrollment
- `api_tokens` – extra support table used for secure Postman Bearer-token login; the seven school tables from your SQL file are retained unchanged

## Project structure
```text
senior-high-api-mysqli/
├── api/index.php
├── api/routes/
│   ├── auth.php
│   ├── students.php
│   ├── subjects.php
│   ├── year_levels.php
│   ├── strands.php
│   ├── student_subjects.php
│   ├── grades.php
│   └── dashboard.php
├── config/database.php
├── functions/database_helpers.php
├── functions/request.php
├── functions/response.php
├── middleware/auth.php
├── database/school_db.sql
├── setup_admin.php
├── .htaccess
└── README.md
```

## Installation (Arch Linux / LAMPP)
1. Extract this folder into `/opt/lampp/htdocs/`.
2. Start Apache and MySQL from the XAMPP/LAMPP manager or run `sudo /opt/lampp/lampp start`.
3. Open `http://localhost/phpmyadmin`.
4. Import `database/school_db.sql`. It creates and selects `senior_high_db`.
5. Open `config/database.php`. Set your MySQL host, database, username and password. Default XAMPP/LAMPP values are `localhost`, `senior_high_db`, `root`, and blank password.
6. Visit `http://localhost/senior-high-api-mysqli/setup_admin.php` once.
7. Admin login: username `admin`, password `admin123`.
8. Delete `setup_admin.php` after creating the admin account.

Windows XAMPP: copy the project to `C:\xampp\htdocs\` and use the same steps.

## Postman setup
Base URL: `http://localhost/senior-high-api-mysqli/api`

1. Send `POST /login` with Body > raw > JSON: `{"username":"admin","password":"admin123"}`.
2. Copy `token` from the response.
3. For protected requests, choose Authorization > Bearer Token and paste the token.
4. Send JSON requests with `Content-Type: application/json`.
5. Tokens expire after eight hours. `POST /logout` revokes the token.

## Endpoints
| Method | Endpoint | Access | Purpose |
|---|---|---|---|
| POST | `/login` | Public | Admin or student login |
| POST | `/logout` | Logged in | Revoke token |
| GET | `/me` | Logged in | Current account |
| GET, POST | `/students` | Admin | List/create students |
| GET, PUT, DELETE | `/students/{student_id}` | Admin; student can view own | Student record |
| GET, POST, PUT, DELETE | `/subjects[/{id}]` | Read: logged in; write: admin | Subject management |
| GET, POST, PUT, DELETE | `/year-levels[/{id}]` | Read: logged in; write: admin | Year levels |
| GET, POST, PUT, DELETE | `/strands[/{id}]` | Read: logged in; write: admin | Strands |
| GET, POST, DELETE | `/student-subjects[/{id}]` | Admin; student reads own | Subject enrollment |
| GET, POST, PUT, DELETE | `/grades[/{id}]` | Read: logged in; write: admin | Quarterly grades |
| GET | `/dashboard` | Admin | Summary counts |

## Example Postman requests

### Create a student (admin token)
`POST /students`
```json
{
  "student_number": "2026-0001",
  "firstname": "Juan",
  "middlename": "Santos",
  "lastname": "Dela Cruz",
  "gender": "Male",
  "birthdate": "2008-05-20",
  "email": "juan@example.com",
  "contact_number": "09123456789",
  "address": "Asingan, Pangasinan",
  "year_level_id": 1,
  "strand_id": 1,
  "enrollment_status": "Enrolled"
}
```
Student username and initial password are both the `student_number`. The password is stored as a secure hash.

### Create a subject
`POST /subjects`
```json
{
  "subject_code": "STEM-MATH11",
  "subject_name": "General Mathematics",
  "description": "Senior High School subject",
  "units": 3,
  "year_level_id": 1,
  "strand_id": 1,
  "semester": "1st Semester"
}
```

### Enroll a student in a subject
`POST /student-subjects`
```json
{"student_id": 1, "subject_id": 1, "school_year": "2026-2027", "semester": "1st Semester"}
```

### Record a quarterly grade
`POST /grades`
```json
{"enrollment_id": 1, "grading_period": "1st Quarter", "grade": 92.50, "remarks": "Passed"}
```

## Important notes
- `student_id` in `students` is the numeric auto-increment primary key. `student_number` is the human-readable student ID used for login.
- `student_subjects.enrollment_id` is the key required when recording a grade.
- Valid grading periods: `1st Quarter`, `2nd Quarter`, `3rd Quarter`, `4th Quarter`.
- Valid semester values: `1st Semester`, `2nd Semester`.
- Valid enrollment statuses: `Enrolled`, `Pending`, `Graduated`, `Dropped`.
- URL rewriting uses Apache `mod_rewrite`; enable it and `AllowOverride All` if clean API routes return 404.
- This is an educational starter API. Restrict CORS and use HTTPS before production use.
