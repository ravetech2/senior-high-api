<?php
// Login accepts the admin username or a student's student number.
function authRoutes(mysqli $db,string $method,string $route,array $data): void {
    if (($route === "login" || $route === "auth/login") && $method === "POST") {
        $username=trim($data["username"] ?? ""); $password=(string)($data["password"] ?? "");
        if ($username === "" || $password === "") json(422,["success"=>false,"message"=>"Username and password are required."]);
        $q=runQuery($db,"SELECT admin_id AS id,username,password,fullname,'admin' AS role FROM admin WHERE username=?",[$username]);
        $user=fetchOneRow($q);
        if (!$user) {
            $q=runQuery($db,"SELECT student_id AS id,student_number AS username,password,CONCAT(firstname,' ',lastname) AS fullname,'student' AS role FROM students WHERE student_number=?",[$username]);
            $user=fetchOneRow($q);
        }
        if (!$user || !password_verify($password,$user["password"])) json(401,["success"=>false,"message"=>"Invalid username or password."]);
        $token=bin2hex(random_bytes(32)); $expires=date("Y-m-d H:i:s",time()+28800);
        runQuery($db,"INSERT INTO api_tokens(user_type,user_id,token_hash,expires_at) VALUES(?,?,?,?)",[$user["role"],(int)$user["id"],hash("sha256",$token),$expires]);
        unset($user["password"]);
        json(200,["success"=>true,"message"=>"Login successful.","token_type"=>"Bearer","token"=>$token,"expires_at"=>$expires,"user"=>$user]);
    }
    if (($route === "logout" || $route === "auth/logout") && $method === "POST") {
        loginRequired($db); $token=bearer();
        runQuery($db,"DELETE FROM api_tokens WHERE token_hash=?",[hash("sha256",$token)]);
        json(200,["success"=>true,"message"=>"Logged out successfully."]);
    }
    if (($route === "me" || $route === "auth/me") && $method === "GET") json(200,["success"=>true,"user"=>loginRequired($db)]);
    json(405,["success"=>false,"message"=>"Method not allowed."]);
}
