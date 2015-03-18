
<?php
include("class.db.php");
$token = $_GET['token'];

$cui = getCUI($token);
//$cui = 20111464;

if(!isset($cui)){
  http_response_code(500);
  return;
}

$db = new db("mysql:host=localhost;dbname=episunsa", "root", "admin123");

//TODO sort by semester
$offeredCourses = $db->select("cursosAbiertos");

$permitedCourses = array();

foreach($offeredCourses as $course){
  $courseID = $course['courseID'];
//  $courseID=1301101;
  if(!approved($courseID) && hasPreRequisites($courseID)){
    $courseDetails = $db->select("cursos", "id=$courseID");
    array_push($permitedCourses, $courseDetails[0]);
  }
}
$data = json_encode($permitedCourses);
print_r($data);

function approved($courseID){
  global $cui;
  global $db;

  $res = $db->select("matriculas","courseID=$courseID && cui=$cui && grade > 10");
  if(count($res) > 0)
    return true;
  $currentCurriculum = ($courseID < 1301101)?"malla02":"malla13";
  $equivalentCucrriculum = ($courseID < 1301101)?"malla13":"malla02";
  $equivalents = $db->select("equivalencias", "$currentCurriculum= $courseID");

  if(count($equivalents) == 0) return false;
  foreach($equivalents as $course){
    $courseID = $course["$equivalentCucrriculum"];
    $res = $db->select("matriculas","courseID=$courseID && cui=$cui && grade > 10");
    if(count($res) == 0) return false;
  }
  return true;
}

function hasPreRequisites($courseID){
  global $cui;
  global $db;
  $prerequisites = $db->select("prerequisitos","courseID=$courseID");
  foreach($prerequisites as $course){
    $courseID = $course["requisite"];
    if(!approved($courseID)) return false;
  }
  return true;
}

function getCUI($token){
  global $db;
  $json = file_get_contents("https://www.googleapis.com/oauth2/v1/tokeninfo?access_token=".$token);
  $data = json_decode($json);
  if(!isset($data->{'error'})){
    $email = $data->{'email'};
    if($email == 'apaz@episunsa.edu.pe'){
      $email = 'alvin.chunga.mamani@gmail.com';
    }
    $res = $db->select("alumnos","email=$email");
    if(count($res) == 0) return null;
    return $res['cui'];
  }
  return null;
}

?>
