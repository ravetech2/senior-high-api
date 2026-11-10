<?php
// Prepare and execute SQL using MySQLi prepared statements.
function runQuery(mysqli $connection, string $sql, array $params = []): mysqli_stmt
{
    $statement = $connection->prepare($sql);
    if ($params) {
        $types = "";
        foreach ($params as $value)
            $types .= is_int($value) ? "i" : (is_float($value) ? "d" : "s");
        $bind = [$types];
        foreach ($params as $key => $value)
            $bind[] = &$params[$key];
        call_user_func_array([$statement, "bind_param"], $bind);
    }
    $statement->execute();
    return $statement;
}
// Return all rows from a SELECT statement.
function fetchAllRows(mysqli_stmt $statement): array
{
    $result = $statement->get_result();
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}
// Return one row from a SELECT statement.
function fetchOneRow(mysqli_stmt $statement): ?array
{
    $result = $statement->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    return $row ?: null;
}
