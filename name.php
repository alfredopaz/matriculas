<?php
include("class.db.php");
$db = new db("mysql:host=192.168.0.2;dbname=episunsa", "root", "admin123");

$token = $_GET['key'];


$cui = getCUI($token);
//$cui = 20111464;
$credits = array(28,28,23,18,13);

$name = $db->select("alumnos", "cui = $cui");
$courses = $db->select("matriculas", 
                       "cui = $cui AND grade < 10 GROUP BY courseID",
                       "",
                       "courseID, MAX(time) as time");
$m = 1;

foreach($courses as $course){
  $courseID = $course['courseID'];
  if(!approved($courseID)){
    $time = $course['time'];
    if($student['time'] > $m){
      $m = $student['time'];
    }
  }
}
print_r(json_encode(array('lastName' => $name[0]['lastName'], 
                          'firstName' => $name[0]['firstName'],
                          'credits' => $credits[$m],
                          'time' => $m)));

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
?>
