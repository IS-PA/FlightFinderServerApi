<?php
$date = "28/11/2015";
$time = "20:46";

$t = strtotime(str_replace('/', '-', $date).' '.$time);

echo $date.' '.$time.'<br>';
echo date("d/m/Y H:i", $t);
