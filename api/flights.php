<?php
ini_set('display_errors','1'); error_reporting(E_ALL);
require('db.php');


$answer = Array();

$answer['status'] = 'ok';
$answer['request'] = getFlights();
echo json_encode($answer);



function getFlights() {
   global $db;
   $stmt = $db->prepare("SELECT * FROM flights");
   $stmt->execute();
   return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
