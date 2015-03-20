
<?php
include("class.db.php");
$token = $_GET['key'];

$db = new db("mysql:host=192.168.0.2;dbname=episunsa", "root", "admin123");
$cui = getCUI($token);

//$cui = 20113629;

$courses = array();

$enrolled = $db->select("prematriculas","cui=$cui");

foreach($enrolled as $course){
  $courses[$course['courseID']] = true;
}

print_r(json_encode(array("ids" => $courses)));

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
