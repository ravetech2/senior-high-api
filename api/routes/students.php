<?php
// Administrator manages student accounts; student login is Student Number + initial password.
function studentRoutes(mysqli $db, string $method, string $route, array $data): void
{
    $p = explode("/", $route);
    $id = isset($p[1]) && ctype_digit($p[1]) ? (int) $p[1] : null;
    if ($method === "GET" && $id === null) {
        adminRequired($db);
        $q = runQuery($db, "SELECT s.*,y.year_level_name,st.strand_code,st.strand_name FROM students s JOIN year_levels y ON y.year_level_id=s.year_level_id JOIN strands st ON st.strand_id=s.strand_id ORDER BY s.student_id DESC");
        json(200, ["success" => true, "data" => fetchAllRows($q)]);
    }
    if ($method === "POST" && $id === null) {
        adminRequired($db);
        $number = trim($data["student_number"] ?? "");
        $first = trim($data["firstname"] ?? "");
        $last = trim($data["lastname"] ?? "");
        $year = (int) ($data["year_level_id"] ?? 0);
        $strand = (int) ($data["strand_id"] ?? 0);
        if (!$number || !$first || !$last || !$year || !$strand)
            json(422, ["success" => false, "message" => "student_number, firstname, lastname, year_level_id and strand_id are required."]);
        $gender = $data["gender"] ?? null;
        if ($gender !== null && !in_array($gender, ["Male", "Female", "Other"], true))
            json(422, ["success" => false, "message" => "Gender must be Male, Female, Other, or null."]);
        $status = $data["enrollment_status"] ?? "Pending";
        if (!in_array($status, ["Enrolled", "Pending", "Graduated", "Dropped"], true))
            json(422, ["success" => false, "message" => "Invalid enrollment_status."]);
        try {
            $db->begin_transaction();
            runQuery($db, "INSERT INTO students(student_number,firstname,middlename,lastname,gender,birthdate,email,password,contact_number,address,year_level_id,strand_id,enrollment_status) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)", [$number, $first, trim($data["middlename"] ?? "") ?: null, $last, $gender, $data["birthdate"] ?? null, trim($data["email"] ?? "") ?: null, password_hash($number, PASSWORD_DEFAULT), trim($data["contact_number"] ?? "") ?: null, trim($data["address"] ?? "") ?: null, $year, $strand, $status]);
            $newId = $db->insert_id;
            $db->commit();
            json(201, ["success" => true, "message" => "Student account created. Initial username and password are the student number.", "student_id" => (int) $newId]);
        } catch (mysqli_sql_exception $e) {
            $db->rollback();
            json(409, ["success" => false, "message" => "Could not create student. Check duplicate student number/email and valid year level/strand."]);
        }
    }
    if ($method === "GET" && $id !== null) {
        $u = loginRequired($db);
        if ($u["role"] !== "admin") {
            $q = runQuery($db, "SELECT student_id FROM students WHERE student_id=?", [(int) $u["id"]]);
            $own = fetchOneRow($q);
            if (!$own || $id !== (int) $u["id"])
                json(403, ["success" => false, "message" => "You may only view your own record."]);
        }
        $q = runQuery($db, "SELECT s.*,y.year_level_name,st.strand_code,st.strand_name FROM students s JOIN year_levels y ON y.year_level_id=s.year_level_id JOIN strands st ON st.strand_id=s.strand_id WHERE s.student_id=?", [$id]);
        $row = fetchOneRow($q);
        if (!$row)
            json(404, ["success" => false, "message" => "Student not found."]);
        unset($row["password"]);
        json(200, ["success" => true, "data" => $row]);
    }
    if ($method === "PUT" && $id !== null) {
        adminRequired($db);
        $q = runQuery($db, "UPDATE students SET firstname=?,middlename=?,lastname=?,gender=?,birthdate=?,email=?,contact_number=?,address=?,year_level_id=?,strand_id=?,enrollment_status=? WHERE student_id=?", [trim($data["firstname"] ?? ""), trim($data["middlename"] ?? "") ?: null, trim($data["lastname"] ?? ""), $data["gender"] ?? null, $data["birthdate"] ?? null, trim($data["email"] ?? "") ?: null, trim($data["contact_number"] ?? "") ?: null, trim($data["address"] ?? "") ?: null, (int) ($data["year_level_id"] ?? 0), (int) ($data["strand_id"] ?? 0), $data["enrollment_status"] ?? "Pending", $id]);
        json(200, ["success" => true, "message" => $q->affected_rows ? "Student updated." : "No changes made or student not found."]);
    }
    if ($method === "DELETE" && $id !== null) {
        adminRequired($db);
        try {
            $q = runQuery($db, "DELETE FROM students WHERE student_id=?", [$id]);
            json(200, ["success" => true, "message" => $q->affected_rows ? "Student deleted." : "Student not found."]);
        } catch (mysqli_sql_exception $e) {
            json(409, ["success" => false, "message" => "Cannot delete this student while enrollments or grades are linked."]);
        }
    }
    json(405, ["success" => false, "message" => "Method not allowed for students."]);
}
