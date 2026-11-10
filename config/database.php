<?php
// MySQLi connection settings. Update these values for your XAMPP/LAMPP setup.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
function db(): mysqli {
    $host = "localhost";
    $database = "senior_high_db";
    $username = "root";
    $password = ""; // Add your MySQL password if one is configured.
    try {
        $connection = new mysqli($host, $username, $password, $database);
        $connection->set_charset("utf8mb4");
        return $connection;
    } catch (mysqli_sql_exception $e) {
        error_log($e->getMessage());
        json(500, ["success" => false, "message" => "Database connection failed. Check config/database.php and MySQL."]);
        throw $e;
    }
}
