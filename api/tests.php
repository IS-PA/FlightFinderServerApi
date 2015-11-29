<?php
$answer = Array(
   'get' => $_GET,
   'post' => $_POST
);

//echo '<pre>', print_r($answer, true), '</pre>'; exit();
echo json_encode($answer);