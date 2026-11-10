<?php
// Admin records grades by enrollment and grading period; students can view their own.
function gradeRoutes(mysqli $db, string $method, string $route, array $data): void
{
    $p = explode("/", $route);
    $id = isset($p[1]) && ctype_digit($p[1]) ? (int) $p[1] : null;
    if ($method === "GET") {
        $u = loginRequired($db);
        $sql = "SELECT g.*,ss.student_id,ss.subject_id,ss.school_year,ss.semester,s.student_number,s.firstname,s.lastname,sub.subject_code,sub.subject_name FROM grades g JOIN student_subjects ss ON ss.enrollment_id=g.enrollment_id JOIN students s ON s.student_id=ss.student_id JOIN subjects sub ON sub.subject_id=ss.subject_id";
        if ($u["role"] === "student") {
            $sql .= " WHERE ss.student_id=? ORDER BY g.grade_id DESC";
            $q = runQuery($db, $sql, [(int) $u["id"]]);
        } elseif ($id) {
            $q = runQuery($db, $sql . " WHERE g.grade_id=?", [$id]);
        } else {
            $q = runQuery($db, $sql . " ORDER BY g.grade_id DESC");
        }
        json(200, ["success" => true, "data" => $id ? fetchOneRow($q) : fetchAllRows($q)]);
    }
    if ($method === "POST" && $id === null) {
        adminRequired($db);
        $en = (int) ($data["enrollment_id"] ?? 0);
        $period = $data["grading_period"] ?? "";
        $grade = $data["grade"] ?? null;
        $valid = ["1st Quarter", "2nd Quarter", "3rd Quarter", "4th Quarter"];
        if (!$en || !in_array($period, $valid, true) || ($grade !== null && (!is_numeric($grade) || $grade < 0 || $grade > 100)))
            json(422, ["success" => false, "message" => "Provide enrollment_id, valid grading_period and grade between 0 and 100 (or null)."]);
        try {
            runQuery($db, "INSERT INTO grades(enrollment_id,grading_period,grade,remarks) VALUES(?,?,?,?)", [$en, $period, $grade, trim($data["remarks"] ?? "") ?: null]);
            json(201, ["success" => true, "message" => "Grade recorded.", "grade_id" => (int) $db->insert_id]);
        } catch (mysqli_sql_exception $e) {
            json(409, ["success" => false, "message" => "A grade for this enrollment and grading period may already exist, or enrollment is invalid."]);
        }
    }
    if ($method === "PUT" && $id) {
        adminRequired($db);
        $grade = $data["grade"] ?? null;
        if ($grade !== null && (!is_numeric($grade) || $grade < 0 || $grade > 100))
            json(422, ["success" => false, "message" => "Grade must be between 0 and 100 or null."]);
        $q = runQuery($db, "UPDATE grades SET grade=?,remarks=? WHERE grade_id=?", [$grade, trim($data["remarks"] ?? "") ?: null, $id]);
        json(200, ["success" => true, "message" => $q->affected_rows ? "Grade updated." : "No changes made or grade not found."]);
    }
    if ($method === "DELETE" && $id) {
        adminRequired($db);
        $q = runQuery($db, "DELETE FROM grades WHERE grade_id=?", [$id]);
        json(200, ["success" => true, "message" => $q->affected_rows ? "Grade deleted." : "Grade not found."]);
    }
    json(405, ["success" => false, "message" => "Method not allowed."]);
}
