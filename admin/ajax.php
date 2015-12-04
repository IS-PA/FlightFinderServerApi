<?php
ini_set('display_errors','1'); error_reporting(E_ALL);
date_default_timezone_set('Europe/Madrid');
require('../api/db.php');
require('../api/utils.php');


//echo checkParams($_POST, Array('origin', 'destination', 'date', 'time'));exit();

$answer = Array();
if (isset($_GET['modifyFlight'])) {
   if (($errorMsg = checkParams($_POST, Array('origin', 'destination', 'date', 'time'))) !== true) {
      $answer['status'] = 'error';
      $answer['msg'] = $errorMsg;
      echo json_encode($answer);
      exit();
   }
   $stmt = $db->prepare("UPDATE flights SET origin=:origin, destination=:destination, departure_timestamp=:departure_timestamp WHERE id=:id");
   $stmt->bindValue(':origin', $_POST['origin'], PDO::PARAM_INT);
   $stmt->bindValue(':destination', $_POST['destination'], PDO::PARAM_INT);
   $stmt->bindValue(':departure_timestamp', strtotime(str_replace('/', '-', $_POST['date']).' '.$_POST['time']), PDO::PARAM_INT);
   $stmt->bindValue(':id', $_POST['id'], PDO::PARAM_INT);
   $stmt->execute();
   $answer['status'] = 'ok';
   $answer['msg'] = 'Updated!';
   echo json_encode($answer);
   exit();
} else if (isset($_GET['addFlight'])) {
   if (($errorMsg = checkParams($_POST, Array('origin', 'destination', 'date', 'time'))) !== true) {
      $answer['status'] = 'error';
      $answer['msg'] = $errorMsg;
      echo json_encode($answer);
      exit();
   }
   $answer['status'] = 'ok';
   $answer['msg'] = 'Done :)';
   echo json_encode($answer);
   exit();
}

$answer['status'] = 'error';
$answer['error'] = 'Method not found';

echo json_encode($answer);
exit();





function checkParams($origin, $check) {
   $missingParams = Array();
   foreach ($check as $param) {
      if (!isset($origin[$param]) || empty($origin[$param])) {
         $missingParams[] = $param;
      }
   }
   if (count($missingParams) != 0) return "Missing params: ".implode(', ', $missingParams);
   
   if (in_array("date", $check)) {
      if (!preg_match('/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/', $origin['date'])) {
         return 'Date should be DD/MM/YYYY';
      }
   }
   
   if (in_array("time", $check)) {
      if (!preg_match("/^[0-9]{2}:[0-9]{2}$/", $origin['time'])) {
         return 'Time should be HH:mm';
      }
   }
   return true;
}