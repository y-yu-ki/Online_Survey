<?php
require "db.php";
$q_key =  "999active-q-key";

$r = get_survey_by_key($q_key, "question_key");
print_r($r);
$json = $r["survey_spec"];
echo "<br><br><br>";
print_r($json);
echo "<br>";
//本番用
echo "<h1>".$r['title']."</h1>";
echo "<p>".$r['survey_spec']["title"]."</p>";
$len = count($json["questions"]);
for ($i=0; $i<$len; $i++){
    echo "<h2>Q.".($i+1)."</h2>";
    echo "<p>".$json["questions"][$i]["label"]."</p>";
    
}