
<?php
include("class.db.php");
$cui = $_GET['cui'];

$db = new db("mysql:host=localhost;dbname=episunsa", "root", "admin123");

//TODO sort by semester
$offeredCourses = $db->select("cursosAbiertos");

$permitedCourses = array();

foreach($offeredCourses as $course){
  $courseID = $course['courseID'];
//  $courseID=1301101;
  if(!approved($courseID) && hasPreRequisites($courseID)){
    $estado = 10;
    $courseDetails = $db->select("matriculas", "courseID=$courseID && cui=$cui");
    if($courseDetails)
      array_push($permitedCourses, $courseDetails[0]);
  }
}
$data = json_encode($permitedCourses);
print_r($data);

$db = null;
$offeredCourses = null;
$permitedCourses = null;
$courseID = null;
$courseDetails = null;
$data = null;

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

?>
