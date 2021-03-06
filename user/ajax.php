<?php
ini_set('display_errors','1'); error_reporting(E_ALL);
header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('Europe/Madrid');
require('../api/db.php');
require('../api/utils.php');

$stmt = $db->prepare("SELECT * FROM flights");
$stmt->execute();
$flights_r = $stmt->fetchAll(PDO::FETCH_ASSOC);
$flights = Array();
foreach ($flights_r as $flight) {
   $flight['seats'] = json_decode($flight['seats'], true);
   $flights[$flight['id']] = $flight;
}

$stmt = $db->prepare("SELECT * FROM airports");
$stmt->execute();
$airports_r = $stmt->fetchAll(PDO::FETCH_ASSOC);
$airports = Array();
$airports_by_country = Array();
foreach ($airports_r as $airport) {
   $airports[$airport['id']] = $airport;
   $airports_by_country[$airport['country']] = $airport;
}
unset($airports_r);

session_start();

if (!isset($_SESSION['loggedin']) || empty($_SESSION['loggedin']) || !$_SESSION['loggedin']) {echo json_encode(Array('status' => 'error', 'msg' => 'Not logged in')); exit();}
if (!isset($_SESSION['userid']) || empty($_SESSION['userid'])) {echo json_encode(Array('status' => 'error', 'msg' => 'Not logged in')); exit();}
$stmt = $db->prepare("SELECT * FROM users WHERE id=:id");
$stmt->bindValue(':id', $_SESSION['userid'], PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (count($users) !== 1) {echo json_encode(Array('status' => 'error', 'msg' => 'Not logged in')); exit();}
$user = $users[0];
$user['buyedFlights'] = json_decode($user['buyedFlights'], true);
unset($users);

$answer = Array();
if (isset($_GET['cancelFlight'])) {
   if (($errorMsg = checkParams($_POST, Array('id', 'seat'))) !== true) {
      $answer['status'] = 'error';
      $answer['msg'] = $errorMsg;
      echo json_encode($answer);
      exit();
   }
   if (!isset($user['buyedFlights'][$_POST['id']])) {
      $answer['status'] = 'error';
      $answer['msg'] = 'You don\'t seem to have bought seats for this flight!';
      echo json_encode($answer);
      exit();
   }
   $seatI = array_search($_POST['seat'], $user['buyedFlights'][$_POST['id']]);
   if ($seatI === false) {
      $answer['status'] = 'error';
      $answer['msg'] = 'Seat not found';
      echo json_encode($answer);
      exit();
   }
   unset($user['buyedFlights'][$_POST['id']][$seatI]);
   if (count($user['buyedFlights'][$_POST['id']]) == 0) {
      unset($user['buyedFlights'][$_POST['id']]);
   }
   $stmt = $db->prepare("UPDATE users SET buyedFlights=:buyedFlights WHERE id=:id");
   $stmt->bindValue(':buyedFlights', json_encode($user['buyedFlights']), PDO::PARAM_STR);
   $stmt->bindValue(':id', $user['id'], PDO::PARAM_INT);
   $stmt->execute();
   
   $flights[$_POST['id']]['seats'][$_POST['seat']] = false;
   $stmt = $db->prepare("UPDATE flights SET seats=:seats WHERE id=:id");
   $stmt->bindValue(':seats', json_encode($flights[$_POST['id']]['seats']), PDO::PARAM_STR);
   $stmt->bindValue(':id', $_POST['id'], PDO::PARAM_INT);
   $stmt->execute();
   
   
   
   $answer['status'] = 'ok';
   $answer['msg'] = 'Canceled!';
   echo json_encode($answer);
   exit();
} else if (isset($_GET['buyFlight'])) {
   
   if (($errorMsg = checkParams($_POST, Array('id', 'seat'))) !== true) {
      $answer['status'] = 'error';
      $answer['msg'] = $errorMsg;
      echo json_encode($answer);
      exit();
   }
   if (!isset($flights[$_POST['id']]['seats'][$_POST['seat']])) {
      $answer['status'] = 'error';
      $answer['msg'] = 'Does that seat exists?';
      echo json_encode($answer);
      exit();
   }
   if ($flights[$_POST['id']]['seats'][$_POST['seat']] !== false) {
      $answer['status'] = 'error';
      $answer['msg'] = 'This seat has already been bought';
      echo json_encode($answer);
      exit();
   }
   
   if (!isset($user['buyedFlights'][$_POST['id']])) $user['buyedFlights'][$_POST['id']] = Array();
   array_push($user['buyedFlights'][$_POST['id']], $_POST['seat']);
   $stmt = $db->prepare("UPDATE users SET buyedFlights=:buyedFlights WHERE id=:id");
   $stmt->bindValue(':buyedFlights', json_encode($user['buyedFlights']), PDO::PARAM_STR);
   $stmt->bindValue(':id', $user['id'], PDO::PARAM_INT);
   $stmt->execute();
   
   
   $flights[$_POST['id']]['seats'][$_POST['seat']] = (int)$user['id'];
   $stmt = $db->prepare("UPDATE flights SET seats=:seats WHERE id=:id");
   $stmt->bindValue(':seats', json_encode($flights[$_POST['id']]['seats']), PDO::PARAM_STR);
   $stmt->bindValue(':id', $_POST['id'], PDO::PARAM_INT);
   $stmt->execute();
   
   
   $answer['status'] = 'ok';
   $answer['msg'] = 'Bought!';
   echo json_encode($answer);
   exit();
}


$answer['status'] = 'error';
$answer['error'] = 'Method not found';

echo json_encode($answer);
exit();