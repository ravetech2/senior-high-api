<?php
// Enroll students in subjects for a school year and semester.
function studentSubjectRoutes(mysqli $db, string $method, string $route, array $data): void
{
    $p = explode("/", $route);
    $id = isset($p[1]) && ctype_digit($p[1]) ? (int) $p[1] : null;
    if ($method === "GET") {
        $u = loginRequired($db);
        if ($u["role"] === "admin") {
            $q = $id ? runQuery($db, "SELECT ss.*,s.student_number,s.firstname,s.lastname,sub.subject_code,sub.subject_name FROM student_subjects ss JOIN students s ON s.student_id=ss.student_id JOIN subjects sub ON sub.subject_id=ss.subject_id WHERE ss.enrollment_id=?", [$id]) : runQuery($db, "SELECT ss.*,s.student_number,s.firstname,s.lastname,sub.subject_code,sub.subject_name FROM student_subjects ss JOIN students s ON s.student_id=ss.student_id JOIN subjects sub ON sub.subject_id=ss.subject_id ORDER BY ss.enrollment_id DESC");
        } else {
            $q = runQuery($db, "SELECT ss.*,sub.subject_code,sub.subject_name FROM student_subjects ss JOIN subjects sub ON sub.subject_id=ss.subject_id JOIN students s ON s.student_id=ss.student_id WHERE s.student_id=? ORDER BY ss.enrollment_id DESC", [(int) $u["id"]]);
        }
        json(200, ["success" => true, "data" => $id ? fetchOneRow($q) : fetchAllRows($q)]);
    }
    if ($method === "POST" && $id === null) {
        adminRequired($db);
        $sid = (int) ($data["student_id"] ?? 0);
        $sub = (int) ($data["subject_id"] ?? 0);
        $year = trim($data["school_year"] ?? "");
        $sem = $data["semester"] ?? "";
        if (!$sid || !$sub || !$year || !in_array($sem, ["1st Semester", "2nd Semester"], true))
            json(422, ["success" => false, "message" => "student_id, subject_id, school_year and valid semester are required."]);
        try {
            runQuery($db, "INSERT INTO student_subjects(student_id,subject_id,school_year,semester) VALUES(?,?,?,?)", [$sid, $sub, $year, $sem]);
            json(201, ["success" => true, "message" => "Student enrolled in subject.", "enrollment_id" => (int) $db->insert_id]);
        } catch (mysqli_sql_exception $e) {
            json(409, ["success" => false, "message" => "Duplicate enrollment or invalid student/subject."]);
        }
    }
    if ($method === "DELETE" && $id) {
        adminRequired($db);
        try {
            $q = runQuery($db, "DELETE FROM student_subjects WHERE enrollment_id=?", [$id]);
            json(200, ["success" => true, "message" => $q->affected_rows ? "Enrollment deleted." : "Enrollment not found."]);
        } catch (mysqli_sql_exception $e) {
            json(409, ["success" => false, "message" => "Delete related grades first."]);
        }
    }
    json(405, ["success" => false, "message" => "Method not allowed."]);
}
