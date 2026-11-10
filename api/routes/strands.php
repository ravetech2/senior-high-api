<?php
// Manage SHS strands such as STEM, ABM, HUMSS, GAS and TVL-ICT.
function strandRoutes(mysqli $db, string $method, string $route, array $data): void
{
    $p = explode("/", $route);
    $id = isset($p[1]) && ctype_digit($p[1]) ? (int) $p[1] : null;
    if ($method === "GET") {
        loginRequired($db);
        $q = $id ? runQuery($db, "SELECT * FROM strands WHERE strand_id=?", [$id]) : runQuery($db, "SELECT * FROM strands ORDER BY strand_code");
        json(200, ["success" => true, "data" => $id ? fetchOneRow($q) : fetchAllRows($q)]);
    }
    if ($method === "POST" && $id === null) {
        adminRequired($db);
        $code = trim($data["strand_code"] ?? "");
        $name = trim($data["strand_name"] ?? "");
        if (!$code || !$name)
            json(422, ["success" => false, "message" => "strand_code and strand_name are required."]);
        try {
            runQuery($db, "INSERT INTO strands(strand_code,strand_name) VALUES(?,?)", [$code, $name]);
            json(201, ["success" => true, "strand_id" => (int) $db->insert_id]);
        } catch (mysqli_sql_exception $e) {
            json(409, ["success" => false, "message" => "Strand code already exists."]);
        }
    }
    if ($method === "PUT" && $id) {
        adminRequired($db);
        $q = runQuery($db, "UPDATE strands SET strand_code=?,strand_name=? WHERE strand_id=?", [trim($data["strand_code"] ?? ""), trim($data["strand_name"] ?? ""), $id]);
        json(200, ["success" => true, "message" => $q->affected_rows ? "Strand updated." : "No changes made or record not found."]);
    }
    if ($method === "DELETE" && $id) {
        adminRequired($db);
        try {
            $q = runQuery($db, "DELETE FROM strands WHERE strand_id=?", [$id]);
            json(200, ["success" => true, "message" => $q->affected_rows ? "Strand deleted." : "Not found."]);
        } catch (mysqli_sql_exception $e) {
            json(409, ["success" => false, "message" => "Strand is assigned to students or subjects."]);
        }
    }
    json(405, ["success" => false, "message" => "Method not allowed."]);
}
