<?php
ini_set('display_errors','1'); error_reporting(E_ALL);
date_default_timezone_set('Europe/Madrid');
require('../api/db.php');
require('../api/utils.php');


$stmt = $db->prepare("SELECT * FROM airports");
$stmt->execute();
$airports = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
} if (isset($_GET['modifyAirport'])) {
   if (($errorMsg = checkParams($_POST, Array('country', 'displayname', 'id'))) !== true) {
      $answer['status'] = 'error';
      $answer['msg'] = $errorMsg;
      echo json_encode($answer);
      exit();
   }
   if (existsAirportWithName($_POST['displayname'], $airports, Array($_POST['id']))) {
      $answer['status'] = 'error';
      $answer['msg'] = 'Some other airport already has that name!';
      echo json_encode($answer);
      exit();
   }
   $stmt = $db->prepare("UPDATE airports SET country=:country, displayname=:displayname WHERE id=:id");
   $stmt->bindValue(':country', $_POST['country'], PDO::PARAM_STR);
   $stmt->bindValue(':displayname', $_POST['displayname'], PDO::PARAM_STR);
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
   $stmt = $db->prepare("INSERT INTO flights(origin, destination, departure_timestamp, seats) VALUES(:origin, :destination, :departure_timestamp, :seats)");
   $stmt->bindValue(':origin', $_POST['origin'], PDO::PARAM_INT);
   $stmt->bindValue(':destination', $_POST['destination'], PDO::PARAM_INT);
   $stmt->bindValue(':departure_timestamp', strtotime(str_replace('/', '-', $_POST['date']).' '.$_POST['time']), PDO::PARAM_INT);
   $stmt->bindValue(':seats', json_encode(Array('1A' => true)), PDO::PARAM_STR);
   $stmt->execute();
   $flightId = $db->lastInsertId();
   $answer['status'] = 'ok';
   $answer['msg'] = 'Added! New flight id: '.$flightId;
   echo json_encode($answer);
   exit();
} else if (isset($_GET['deleteFlight'])) {
   if (($errorMsg = checkParams($_POST, Array('id'))) !== true) {
      $answer['status'] = 'error';
      $answer['msg'] = $errorMsg;
      echo json_encode($answer);
      exit();
   }
   $stmt = $db->prepare("DELETE FROM flights WHERE id=:id");
   $stmt->bindValue(':id', $_POST['id'], PDO::PARAM_INT);
   $stmt->execute();
   $answer['status'] = 'ok';
   $answer['msg'] = 'Deleted!';
   echo json_encode($answer);
   exit();
}  else if (isset($_GET['addAirport'])) {
   if (($errorMsg = checkParams($_POST, Array('country', 'displayname'))) !== true) {
      $answer['status'] = 'error';
      $answer['msg'] = $errorMsg;
      echo json_encode($answer);
      exit();
   }
   if (existsAirportWithName($_POST['displayname'], $airports)) {
      $answer['status'] = 'error';
      $answer['msg'] = 'Some other airport already has that name!';
      echo json_encode($answer);
      exit();
   }
   $stmt = $db->prepare("INSERT INTO airports(country, displayname) VALUES(:country, :displayname)");
   $stmt->bindValue(':country', $_POST['country'], PDO::PARAM_STR);
   $stmt->bindValue(':displayname', $_POST['displayname'], PDO::PARAM_STR);
   $stmt->execute();
   $airportId = $db->lastInsertId();
   $answer['status'] = 'ok';
   $answer['msg'] = 'Added! New airport id: '.$airportId;
   echo json_encode($answer);
   exit();
} else if (isset($_GET['deleteAirport'])) {
   if (($errorMsg = checkParams($_POST, Array('id'))) !== true) {
      $answer['status'] = 'error';
      $answer['msg'] = $errorMsg;
      echo json_encode($answer);
      exit();
   }
   //echo $_POST['id']; exit();
   
   $stmt = $db->prepare("DELETE FROM airports WHERE id=:id");
   $stmt->bindValue(':id', $_POST['id'], PDO::PARAM_INT);
   $stmt->execute();
   $answer['status'] = 'ok';
   $answer['msg'] = 'Deleted!';
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

function existsAirportWithName($name, $airports, $ignoreAirports = Array()) {
   foreach ($airports as $airport) {
      if (($airport['displayname'] == $name) && !in_array($airport['id'], $ignoreAirports)) return true;
   }
   return false;
}