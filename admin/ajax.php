<?php
ini_set('display_errors','1'); error_reporting(E_ALL);
date_default_timezone_set('Europe/Madrid');
require('../api/db.php');
require('../api/utils.php');


$answer = Array();
if (isset($_GET['modifyFlight'])) {
   $stmt = $db->prepare("UPDATE flights SET origin=:origin, destination=:destination, departure_timestamp=:departure_timestamp WHERE id=:id");
   $stmt->bindValue(':origin', $_POST['origin'], PDO::PARAM_INT);
   $stmt->bindValue(':destination', $_POST['destination'], PDO::PARAM_INT);
   $stmt->bindValue(':departure_timestamp', strtotime(str_replace('/', '-', $_POST['date']).' '.$_POST['time']), PDO::PARAM_INT);
   $stmt->bindValue(':id', $_POST['id'], PDO::PARAM_INT);
   $stmt->execute();
   $answer['status'] = 'ok';
   $answer['request'] = 'Done';
   echo json_encode($answer);
   exit();
}

$answer['status'] = 'error';
$answer['error'] = 'Flight not found';

echo json_encode($answer);
exit();