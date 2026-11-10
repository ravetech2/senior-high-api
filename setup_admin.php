<?php
// Run once in a browser to create the first administrator account.
require_once __DIR__ . "/functions/response.php";
require_once __DIR__ . "/functions/database_helpers.php";
require_once __DIR__ . "/config/database.php";
$db = db();
$username = "admin";
$password = "admin123";
$q = runQuery($db, "SELECT admin_id FROM admin WHERE username=?", [$username]);
if (fetchOneRow($q))
    exit("Admin already exists. Delete setup_admin.php now.");
runQuery($db, "INSERT INTO admin(username,password,fullname) VALUES(?,?,?)", [$username, password_hash($password, PASSWORD_DEFAULT), "System Administrator"]);
echo "Admin account created.<br>Username: admin<br>Password: admin123<br><strong>Delete setup_admin.php immediately.</strong>";
