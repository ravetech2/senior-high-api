<?php
// Read and manage Grade 11 and Grade 12 year levels.
function yearLevelRoutes(mysqli $db,string $method,string $route,array $data): void {
 $p=explode("/",$route);$id=isset($p[1])&&ctype_digit($p[1])?(int)$p[1]:null;
 if($method==="GET"){loginRequired($db);$q=$id?runQuery($db,"SELECT * FROM year_levels WHERE year_level_id=?",[$id]):runQuery($db,"SELECT * FROM year_levels ORDER BY year_level_id");$rows=$id?fetchOneRow($q):fetchAllRows($q);json(200,["success"=>true,"data"=>$rows]);}
 if($method==="POST"&&$id===null){adminRequired($db);$name=trim($data["year_level_name"]??"");if(!$name)json(422,["success"=>false,"message"=>"year_level_name is required."]);try{runQuery($db,"INSERT INTO year_levels(year_level_name) VALUES(?)",[$name]);json(201,["success"=>true,"year_level_id"=>(int)$db->insert_id]);}catch(mysqli_sql_exception $e){json(409,["success"=>false,"message"=>"Year level already exists."]);}}
 if($method==="PUT"&&$id){adminRequired($db);$q=runQuery($db,"UPDATE year_levels SET year_level_name=? WHERE year_level_id=?",[trim($data["year_level_name"]??""),$id]);json(200,["success"=>true,"message"=>$q->affected_rows?"Year level updated.":"No changes made or record not found."]);}
 if($method==="DELETE"&&$id){adminRequired($db);try{$q=runQuery($db,"DELETE FROM year_levels WHERE year_level_id=?",[$id]);json(200,["success"=>true,"message"=>$q->affected_rows?"Year level deleted.":"Not found."]);}catch(mysqli_sql_exception $e){json(409,["success"=>false,"message"=>"Year level is referenced by students or subjects."]);}}
 json(405,["success"=>false,"message"=>"Method not allowed."]);
}
