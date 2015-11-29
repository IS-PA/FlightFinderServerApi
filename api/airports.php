<?php
ini_set('display_errors','1'); error_reporting(E_ALL);
require('db.php');

$answer = Array();

if (isset($_GET['arg1']) && ($_GET['arg1'] == "add")) {
   if (isset($_POST['object'])) {
      $answer['status'] = 'ok';
      $answer['request'] = addAirport(json_decode($_POST['object'], true));
   } else {
      $answer['status'] = 'error';
      $answer['error'] = 'Object not found';
   }
   echo json_encode($answer);
   exit();
} else if (isset($_GET['arg1']) && ($_GET['arg1'] == "delete")) {
   if (isset($_POST['object'])) {
      $answer['status'] = 'ok';
      $answer['request'] = deleteAirport($_POST['object']);
   } else {
      $answer['status'] = 'error';
      $answer['error'] = 'Object not found';
   }
   echo json_encode($answer);
   exit();
} else if (!isset($_GET['arg1']) || ($_GET['arg1'] == "get")) {
   $answer['status'] = 'ok';
   $answer['request'] = getAirports();
   echo json_encode($answer);
   exit();
}

$answer['status'] = 'error';
$answer['error'] = 'Undefined method';
echo json_encode($answer);


function getAirports() {
   global $db;
   $stmt = $db->prepare("SELECT * FROM airports");
   $stmt->execute();
   return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function addAirport($airport) {
   global $db;
   $stmt = $db->prepare("INSERT INTO airports(country, displayname) VALUES(:country, :displayname)");
   $stmt->bindValue(':country', $airport['country'], PDO::PARAM_STR);
   $stmt->bindValue(':displayname', $airport['displayname'], PDO::PARAM_STR);
   $stmt->execute();
   return $db->lastInsertId();
}

function deleteAirport($id) {
   global $db;
   $stmt = $db->prepare("DELETE FROM airports WHERE id=:id");
   $stmt->bindValue(':id', $id, PDO::PARAM_INT);
   $stmt->execute();
   return true;
}