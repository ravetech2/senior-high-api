<?php
// Return summary counts for the admin dashboard.
function dashboardRoutes(mysqli $db, string $method, string $route, array $data): void
{
    if ($method !== "GET")
        json(405, ["success" => false, "message" => "Use GET for dashboard."]);
    adminRequired($db);
    $stats = [];
    foreach (["students", "subjects", "year_levels", "strands", "student_subjects", "grades"] as $table) {
        $stats[$table] = (int) $db->query("SELECT COUNT(*) FROM `$table`")->fetch_row()[0];
    }
    json(200, ["success" => true, "statistics" => $stats]);
}
