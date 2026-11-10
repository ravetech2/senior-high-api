<?php
// Resolve an API token and load its admin or student account.
function currentUser(mysqli $db): ?array {
    $token = bearer();
    if (!$token) return null;
    $q = runQuery($db, "SELECT user_type,user_id FROM api_tokens WHERE token_hash=? AND expires_at>NOW() LIMIT 1", [hash("sha256", $token)]);
    $record = fetchOneRow($q);
    if (!$record) return null;
    if ($record["user_type"] === "admin") {
        $q = runQuery($db, "SELECT admin_id AS id,username,fullname,'admin' AS role FROM admin WHERE admin_id=?", [(int)$record["user_id"]]);
    } else {
        $q = runQuery($db, "SELECT student_id AS id,student_number AS username,firstname,middlename,lastname,'student' AS role FROM students WHERE student_id=?", [(int)$record["user_id"]]);
    }
    return fetchOneRow($q);
}
// Require a valid logged-in admin or student.
function loginRequired(mysqli $db): array {
    $user = currentUser($db);
    if (!$user) json(401, ["success"=>false,"message"=>"Unauthorized. Login and send a valid Bearer token."]);
    return $user;
}
// Restrict a route to administrator accounts.
function adminRequired(mysqli $db): array {
    $user = loginRequired($db);
    if ($user["role"] !== "admin") json(403,["success"=>false,"message"=>"Administrator access required."]);
    return $user;
}
