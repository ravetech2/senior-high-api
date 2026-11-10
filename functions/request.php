<?php
// Read JSON request body from Postman.
function body(): array
{
    $data = json_decode(file_get_contents("php://input"), true);
    return is_array($data) ? $data : $_POST;
}
// Read the Bearer token from the Authorization header.
function bearer(): ?string
{
    $h = function_exists("getallheaders") ? getallheaders() : [];
    $v = $h["Authorization"] ?? $h["authorization"] ?? "";
    return preg_match('/Bearer\s+(.+)/i', $v, $m) ? trim($m[1]) : null;
}
