<?php
ini_set('display_errors','1'); error_reporting(E_ALL);
require('db.php');

$flight_id = $_GET['arg1'];
$answer = Array();

$flight = getFlightById($flight_id);

if ($flight) {
   if (isset($_GET['arg2']) && !empty($_GET['arg2'])) {
      $flight_get = $_GET['arg2'];
      if (isset($flight[$flight_get]) && !empty($flight[$flight_get])) {
         $answer['status'] = 'ok';
         $answer['request'] = $flight[$flight_get];
      } else {
         $answer['status'] = 'error';
         $answer['error'] = 'Property not found';
      }
   } else {
      $answer['status'] = 'ok';
      $answer['request'] = $flight;
   }
} else {
   $answer['status'] = 'error';
   $answer['error'] = 'Flight not found';
}
echo json_encode($answer);








function getFlightById($id) {
   global $db;
   $stmt = $db->prepare("SELECT * FROM flights WHERE id=?");
   $stmt->bindValue(1, $id, PDO::PARAM_INT);
   $stmt->execute();
   if ($stmt->rowCount() == 1) {
      $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
      if (count($rows) == 1) {
         if (isset($rows[0]['id'])) {
            $rows[0]['seats'] = json_decode($rows[0]['seats']);
            return $rows[0];
         }
      } 
   }
   return false;
}


