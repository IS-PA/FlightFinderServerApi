<?php

function getAirportNameById($id, $airports) {
   if (isset($airports[$id]) && !empty($airports[$id])) {
      if (isset($airports[$id]['displayname']) && !empty($airports[$id]['displayname'])) {
         return $airports[$id]['displayname'];
      }
   }
   return "???";
}

function getAirportCountryById($id, $airports) {
   if (isset($airports[$id]) && !empty($airports[$id])) {
      if (isset($airports[$id]['country']) && !empty($airports[$id]['country'])) {
         return $airports[$id]['country'];
      }
   }
   return "???";
}



function checkParams($origin, $check) {
   $missingParams = Array();
   foreach ($check as $param) {
      if (!isset($origin[$param]) || (empty($origin[$param]) && $origin[$param] != 0)) {
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
