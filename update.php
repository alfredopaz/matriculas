
<?php
include("class.db.php");

$config = include('config.php');
$db_type = $config['db_type'];
$db_host = $config['db_host'];
$db_name = $config['db_name'];
$db_user = $config['db_user'];
$db_pass = $config['db_pass'];

$db = new db("$db_type:host=$db_host;dbname=$db_name", "$db_user", "$db_pass");

$token = $_GET['key'];
$courses = json_decode($_GET['chosen']);
$cui = getCUI($token);

if(empty($cui)){
  http_response_code(500);
  return;
}

foreach($courses as $courseID => $status){
  if($status){
    $db->insert("prematriculas",array("cui" => $cui, "courseID" => $courseID));
  }else{
    $db->delete("prematriculas","cui=$cui && courseID=$courseID");
  }
}
print_r("sucess");

function getCUI($token){
  global $db;
  $json = file_get_contents("https://www.googleapis.com/oauth2/v1/tokeninfo?access_token=".$token);
  $data = json_decode($json);
  if(!isset($data->{'error'})){
    $email = $data->{'email'};
    if($email == 'apaz@episunsa.edu.pe'){
      $email = 'alvin.chunga.mamani@gmail.com';
    }
    $res = $db->select("alumnos","email='$email'");
    if(count($res) == 0) return null;
    return $res[0]['cui'];
  }
  return null;
}

?>
