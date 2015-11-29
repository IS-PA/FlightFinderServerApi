<?php
ini_set('display_errors','1'); error_reporting(E_ALL);
require('db.php');

$airport_id = $_GET['arg1'];
$answer = Array();

$airport = getAirportById($airport_id);

if ($airport) {
   if (isset($_GET['arg2']) && !empty($_GET['arg2'])) {
      $airport_prop = $_GET['arg2'];
      if (isset($flight[$airport_prop]) && !empty($flight[$airport_prop])) {
         $answer['status'] = 'ok';
         $answer['request'] = $flight[$airport_prop];
      } else {
         $answer['status'] = 'error';
         $answer['error'] = 'Property not found';
      }
   } else {
      $answer['status'] = 'ok';
      $answer['request'] = $airport;
   }
} else {
   $answer['status'] = 'error';
   $answer['error'] = 'Flight not found';
}
echo json_encode($answer);








function getAirportById($id) {
   global $db;
   $stmt = $db->prepare("SELECT * FROM airports WHERE id=?");
   $stmt->bindValue(1, $id, PDO::PARAM_INT);
   $stmt->execute();
   if ($stmt->rowCount() == 1) {
      $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
      if (count($rows) == 1) {
         if (isset($rows[0]['id'])) {
            return $rows[0];
         }
      } 
   }
   return false;
}


