
<?php
include("class.db.php");
$cui = $_GET['cui'];
$courses = json_decode($_GET['chosen']);
$db = new db("mysql:host=localhost;dbname=episunsa", "root", "admin123");

foreach($courses as $courseID => $status){
  if($status){
    $db->insert("prematriculas",array("cui" => $cui, "courseID" => $courseID));
  }else{
    $db->delete("prematriculas","cui=$cui && courseID=$courseID");
  }
}
print_r("sucess");
?>
