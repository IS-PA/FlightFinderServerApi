<?php
ini_set('display_errors','1'); error_reporting(E_ALL);
require('db.php');


$answer = Array();

$answer['status'] = 'ok';
$answer['request'] = getAirports();
echo json_encode($answer);



function getAirports() {
   global $db;
   $stmt = $db->prepare("SELECT * FROM airports");
   $stmt->execute();
   return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
