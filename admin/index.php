<?php
ini_set('display_errors','1'); error_reporting(E_ALL);
date_default_timezone_set('Europe/Madrid');
require('../api/db.php');
require('../api/utils.php');


$stmt = $db->prepare("SELECT * FROM flights");
$stmt->execute();
$flights = $stmt->fetchAll(PDO::FETCH_ASSOC);


$stmt = $db->prepare("SELECT * FROM airports");
$stmt->execute();
$airports_r = $stmt->fetchAll(PDO::FETCH_ASSOC);
$airports = Array();
foreach ($airports_r as $airport) {
   $airports[$airport['id']] = $airport;
}
unset($airports_r);


?><!DOCTYPE html>
<html>
   <head>
      <title>Admin Panel: Flight Finder</title>
      <meta charset="UTF-8">
      <!--<meta name="viewport" content="width=device-width, initial-scale=1.0">-->
      
      <link rel="stylesheet" href="//code.jquery.com/ui/1.11.3/themes/smoothness/jquery-ui.css">
      <link rel="stylesheet" href="https://cdn.datatables.net/1.10.10/css/dataTables.jqueryui.min.css">
      <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
      <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
      <script src="../js/datepicker-es.js"></script>
      <script src="https://cdn.datatables.net/1.10.10/js/jquery.dataTables.min.js"></script>
      <script src="https://cdn.datatables.net/1.10.10/js/dataTables.jqueryui.min.js"></script>
      
      
      <style>
         .adminIcon {
            cursor: pointer;
         }
         
         .infolink {
            cursor: help;
         }
      </style>
      <script>
         $(document).ready(function(){
            $('#flights').dataTable({
               'iDisplayLength': 100,
               "order": [[ 0, "asc" ]],
               "aoColumnDefs": [{
                     "bSortable": false,
                     "aTargets": [4, 5]
                  }]
            });
            $('#airports').dataTable({
               'iDisplayLength': 100,
               "order": [[ 0, "asc" ]],
               "aoColumnDefs": [{
                     "bSortable": false,
                     "aTargets": [3]
                  }]
            });
            $(".addFlight").click(function(){
               
            });
            
            $(".modifyFlight").click(function(){
               var currentFlight = this;
               var popup = $('<div title="Modifying Flight!">Flight #' + currentFlight.dataset.flightId + '<br>\
                  <form id="modifyFlight_form" method="post"><fieldset>\
                     <select name="origin" id="modifyFlight_form_select_origin" required><option disabled selected> -- Select an origin -- </option><?php
                        foreach ($airports as $airport) {
                           if (!$airport['id'] == 0) {
                              echo '<option value='.$airport['id'].'>'.htmlspecialchars($airport['displayname'], ENT_QUOTES)."</option>";
                           }
                        }
                     ?></select> <label for="modifyFlight_form_select_origin">Origin</label><br>\
                     <select name="destination" id="modifyFlight_form_select_destination" required><option disabled selected> -- Select a destination -- </option><?php
                        foreach ($airports as $airport) {
                           if (!$airport['id'] == 0) {
                              echo '<option value='.$airport['id'].'>'.htmlspecialchars($airport['displayname'], ENT_QUOTES)."</option>";
                           }
                        }
                     ?></select> <label for="modifyFlight_form_select_destination">Destination</label><br>\
                     <input type="text" id="modifyFlight_form_date" name="date" value="'+currentFlight.dataset.flightDate+'" required> <label for="modifyFlight_form_date">Date (DD/MM/YYYY)</label><br>\
                     <input type="text" id="modifyFlight_form_time" name="time" value="'+currentFlight.dataset.flightTime+'" placeholder="HH:mm" required> <label for="modifyFlight_form_time">Time (HH:mm)</label><br>\
                     <input type="hidden" name="id" value="'+currentFlight.dataset.flightId+'">\
                  </fieldset></form>\
                  <p id="popupMsg">...</p>\
                  </div>');
               popup.find("form").find("select#modifyFlight_form_select_origin").find("[value=" + currentFlight.dataset.flightOrigin + "]").attr('selected','selected');
               popup.find("form").find("select#modifyFlight_form_select_destination").find("[value=" + currentFlight.dataset.flightDestination + "]").attr('selected','selected');
               popup.find("form").find("input#modifyFlight_form_date").datepicker($.datepicker.regional["es"]);
               popup.dialog({
                  modal: true,
                  width: 600,
                  buttons: {
                     "Update": function(){
                        sendAjaxRequest("ajax.php", "modifyFlight", $("#modifyFlight_form").serialize(), {'dialog':this, 'popup':popup},function(data, cb_data){
                           console.log(data);
                           if (data["status"] === "ok") {
                              popup.animate({backgroundColor: "rgb(0, 255, 0, 0.3)"},1000);
                              $(cb_data['popup']).find("#popupMsg").text(data["msg"]);
                              $(cb_data['dialog']).dialog('option', 'hide', 'fold');
                              window.setTimeout(function() {$(cb_data['dialog']).dialog("close");}, 2000);
                              window.setTimeout(function() {location.reload();}, 2750);
                           } else if (data["status"] === "error") {
                              popup.animate({backgroundColor: "rgb(255, 0, 0, 0.3)"},1000);
                              $(cb_data['popup']).find("#popupMsg").text(data["msg"]);
                              window.setTimeout(function() {popup.animate({backgroundColor: "#ffffff"},1000);}, 2000);
                           }
                        });
                     },
                     "Cancel": function(){
                        $(this).dialog('option', 'hide', 'fade');
                        $(this).dialog("close");
                     }
                  },
                  show: "fold",
                  hide: "scale"
               });
            });
            
            $(".deleteFlight").click(function(){
               //console.log($(this));
               var caja = $('<div title="Deleting Flight!"><h1>[Not finished]</h1><br><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Flight #' + this.dataset.flightId + ' will be permanently deleted and cannot be recovered. Are you sure?</div>');
               caja.dialog({
                  modal: true,
                  //height:140,
                  width: 600,
                  buttons: {
                     "Delete": function(){
                        alert("I said\nNot finished!!!!");
                        $(this).dialog('option', 'hide', 'explode');
                        //$(this).dialog("close");
                     },
                     "Cancel": function(){
                        $(this).dialog('option', 'hide', 'fade');
                        $(this).dialog("close");
                     }
                  },
                  show: "pulsate",
                  hide: "scale"
               });
            });
         });
         
         function sendAjaxRequest(url, getData, postData, cb_data, cb) {
            $.ajax({
               type: "POST",
               url: url + '?' + getData,
               data: postData,
               dataType: "json",
               success: function(data, status) {
                  //console.log(data);//return;
                  cb(data, cb_data);
               },
               error: function(request, status) {
                  cb(request.responseText, cb_data);
               }
            });
         }
         
      </script>
   </head>
   <body>
      <h3>Flights (#<?php echo count($flights)-1; ?>) <img src="../img/add_icon.png" class="addFlight adminIcon" title="Add new flight" class="adminIcon"></h3> 
      <table id="flights" class="display" cellspacing="0" width="100%">
         <thead>
            <tr>
               <th>ID</th>
               <th>Origin</th>
               <th>Destination</th>
               <th>Departure</th>
               <th>Seats</th>
               <th>Administrate</th>
            </tr>
         </thead>
         <tfoot>
            <tr>
               <th>ID</th>
               <th>Origin</th>
               <th>Destination</th>
               <th>Departure</th>
               <th>Seats</th>
               <th>Administrate</th>
            </tr>
         </tfoot>
         <tbody>
            <?php
            foreach ($flights as $flight) {
               if ($flight['id'] == 0) continue;
               echo '<tr>'
                        . '<td><a name="flight-'. $flight['id'] .'"></a>'. $flight['id'] .'</td>'
                        . '<td><a class="infolink" href="#airport-'.$flight['origin'].'">['.$flight['origin'].']'. getAirportNameById($flight['origin'], $airports) .' ('.getAirportCountryById($flight['origin'], $airports).')</a></td>'
                        . '<td><a class="infolink" href="#airport-'.$flight['destination'].'">['.$flight['destination'].']'. getAirportNameById($flight['destination'], $airports) .' ('.getAirportCountryById($flight['destination'], $airports).')</a></td>'
                        . '<td>'. date("d/m/Y\<br> H:i", $flight['departure_timestamp']) .'</td>'
                        . '<td>'. $flight['seats'] .'</td>'
                        . '<td style="text-align:center;">'
                           . '<img src="../img/modify_icon.png" class="modifyFlight adminIcon" title="Modify flight" data-flight-id="'.$flight['id'].'" data-flight-origin='.$flight['origin'].' data-flight-destination='.$flight['destination'].' data-flight-date="'.date("d/m/Y", $flight['departure_timestamp']).'" data-flight-time="'.date("H:i", $flight['departure_timestamp']).'">'
                           . '<img src="../img/delete_icon.png" class="deleteFlight adminIcon" title="Delete flight" data-flight-id="'.$flight['id'].'" style="margin-left:25px;">'
                           . '</td>'
                     . '</tr>';
            }
            ?>
         </tbody>
      </table>
      
      
      <h3>Airports (#<?php echo count($airports)-1; ?>)</h3>
      <table id="airports" class="display" cellspacing="0" width="100%">
         <thead>
            <tr>
               <th>ID</th>
               <th>Country</th>
               <th>Name</th>
               <th>Administrate</th>
            </tr>
         </thead>
         <tfoot>
            <tr>
               <th>ID</th>
               <th>Country</th>
               <th>Name</th>
               <th>Administrate</th>
            </tr>
         </tfoot>
         <tbody>
            <?php
            foreach ($airports as $airport) {
               if ($airport['id'] == 0) continue;
               echo '<tr>'
                        . '<td><a name="airport-'. $airport['id'] .'"></a>'. $airport['id'] .'</td>'
                        . '<td>'. $airport['country'] .'</td>'
                        . '<td>'. $airport['displayname'] .'</td>'
                        . '<td style="text-align:center;">'
                           . '<img src="../img/modify_icon.png" title="">'
                           . '<img src="../img/delete_icon.png">'
                           . '</td>'
                     . '</tr>';
            }
            ?>
         </tbody>
      </table>
      <br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
   </body>
</html>
