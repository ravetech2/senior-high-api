<?php
// Manage subjects and connect them to a year level and optional strand.
function subjectRoutes(mysqli $db, string $method, string $route, array $data): void
{
    $p = explode("/", $route);
    $id = isset($p[1]) && ctype_digit($p[1]) ? (int) $p[1] : null;
    if ($method === "GET" && $id === null) {
        loginRequired($db);
        $q = runQuery($db, "SELECT s.*,y.year_level_name,st.strand_code FROM subjects s JOIN year_levels y ON y.year_level_id=s.year_level_id LEFT JOIN strands st ON st.strand_id=s.strand_id ORDER BY s.subject_code");
        json(200, ["success" => true, "data" => fetchAllRows($q)]);
    }
    if ($method === "POST" && $id === null) {
        adminRequired($db);
        $code = trim($data["subject_code"] ?? "");
        $name = trim($data["subject_name"] ?? "");
        $year = (int) ($data["year_level_id"] ?? 0);
        $sem = $data["semester"] ?? "";
        if (!$code || !$name || !$year || !in_array($sem, ["1st Semester", "2nd Semester"], true))
            json(422, ["success" => false, "message" => "subject_code, subject_name, year_level_id and valid semester are required."]);
        try {
            runQuery($db, "INSERT INTO subjects(subject_code,subject_name,description,units,year_level_id,strand_id,semester) VALUES(?,?,?,?,?,?,?)", [$code, $name, trim($data["description"] ?? "") ?: null, (float) ($data["units"] ?? 3), $year, isset($data["strand_id"]) ? (int) $data["strand_id"] : null, $sem]);
            json(201, ["success" => true, "message" => "Subject created.", "subject_id" => (int) $db->insert_id]);
        } catch (mysqli_sql_exception $e) {
            json(409, ["success" => false, "message" => "Subject code already exists or year level/strand is invalid."]);
        }
    }
    if ($method === "PUT" && $id !== null) {
        adminRequired($db);
        $q = runQuery($db, "UPDATE subjects SET subject_code=?,subject_name=?,description=?,units=?,year_level_id=?,strand_id=?,semester=? WHERE subject_id=?", [trim($data["subject_code"] ?? ""), trim($data["subject_name"] ?? ""), trim($data["description"] ?? "") ?: null, (float) ($data["units"] ?? 3), (int) ($data["year_level_id"] ?? 0), isset($data["strand_id"]) ? (int) $data["strand_id"] : null, $data["semester"] ?? "1st Semester", $id]);
        json(200, ["success" => true, "message" => $q->affected_rows ? "Subject updated." : "No changes made or subject not found."]);
    }
    if ($method === "DELETE" && $id !== null) {
        adminRequired($db);
        try {
            $q = runQuery($db, "DELETE FROM subjects WHERE subject_id=?", [$id]);
            json(200, ["success" => true, "message" => $q->affected_rows ? "Subject deleted." : "Subject not found."]);
        } catch (mysqli_sql_exception $e) {
            json(409, ["success" => false, "message" => "Cannot delete a subject linked to student enrollments."]);
        }
    }
    json(405, ["success" => false, "message" => "Method not allowed for subjects."]);
}
