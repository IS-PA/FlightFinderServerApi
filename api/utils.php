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