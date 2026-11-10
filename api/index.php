<?php
// Main API entry point. Every endpoint is dispatched to its own route file.
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *"); // Restrict this before production deployment.
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(204);
    exit;
}
require_once __DIR__ . "/../functions/response.php";
require_once __DIR__ . "/../functions/request.php";
require_once __DIR__ . "/../functions/database_helpers.php";
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../middleware/auth.php";
$db = db();
$method = $_SERVER["REQUEST_METHOD"];
$route = trim($_GET["route"] ?? "", "/");
$data = body();
$module = explode("/", $route)[0];
$routes = [
    "auth" => ["routes/auth.php", "authRoutes"],
    "login" => ["routes/auth.php", "authRoutes"],
    "logout" => ["routes/auth.php", "authRoutes"],
    "me" => ["routes/auth.php", "authRoutes"],
    "students" => ["routes/students.php", "studentRoutes"],
    "subjects" => ["routes/subjects.php", "subjectRoutes"],
    "year-levels" => ["routes/year_levels.php", "yearLevelRoutes"],
    "strands" => ["routes/strands.php", "strandRoutes"],
    "student-subjects" => ["routes/student_subjects.php", "studentSubjectRoutes"],
    "grades" => ["routes/grades.php", "gradeRoutes"],
    "dashboard" => ["routes/dashboard.php", "dashboardRoutes"]
];
if (isset($routes[$module])) {
    require __DIR__ . "/" . $routes[$module][0];
    $fn = $routes[$module][1];
    $fn($db, $method, $route, $data);
}
json(404, ["success" => false, "message" => "API endpoint not found.", "route" => $route]);
