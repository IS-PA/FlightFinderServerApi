<?php
ini_set('display_errors','1'); error_reporting(E_ALL);
header('Content-Type: text/html; charset=utf-8');
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
$airports_by_country = Array();
foreach ($airports_r as $airport) {
   $airports[$airport['id']] = $airport;
   $airports_by_country[$airport['country']] = $airport;
}
unset($airports_r);

session_start();

//$_POST['password'] = 'password';

if (!isset($_SESSION['loggedin']) || empty($_SESSION['loggedin'])) $_SESSION['loggedin'] = false;
if (isset($_GET['login']) && isset($_POST['username']) && !empty($_POST['username']) && isset($_POST['password']) && !empty($_POST['password'])) {
   $stmt = $db->prepare("SELECT * FROM users WHERE username=:username");
   $stmt->bindValue(':username', $_POST['username'], PDO::PARAM_STR);
   $stmt->execute();
   $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
   if (count($users) == 1) {
      if ($users[0]['password'] == hash('sha256', $_POST['password'])) {
         $_SESSION['loggedin'] = true;
         $_SESSION['userid'] = $users[0]['id'];
      }
   }
}
if (isset($_GET['logout'])) {
   $_SESSION['loggedin'] = false;
}

if (isset($_GET['logout']) || isset($_GET['login'])) {
   header('Location: '.$_SERVER['PHP_SELF']);
   echo 'Redirecting to -> '.$_SERVER['PHP_SELF'];
   exit();
}

if ($_SESSION['loggedin']) {
   if (!isset($_SESSION['userid']) || empty($_SESSION['userid'])) {$_SESSION['loggedin'] = false; exit();}
   $stmt = $db->prepare("SELECT * FROM users WHERE id=:id");
   $stmt->bindValue(':id', $_SESSION['userid'], PDO::PARAM_INT);
   $stmt->execute();
   $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
   if (count($users) !== 1) {$_SESSION['loggedin'] = false; exit();}
   $user = $users[0];
   $user['buyedFlights'] = json_decode($user['buyedFlights'], true);
   unset($users);
}


/*
foreach ($user['buyedFlights'] as $flightId => $seatsBuyed) {
   $flight = $flights[$flightId];
}
print_r($flights[2]);
exit();
*/

?><!DOCTYPE html>
<html>
<head>
   <title>Flight Finder</title>
   <meta charset="UTF-8">
   <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
   <link rel="icon" href="favicon.ico" type="image/x-icon">

   <link rel="stylesheet" href="//cdn.datatables.net/1.10.10/css/dataTables.jqueryui.min.css">
   <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
   <script src="https://files.devpgsv.com/libs/jquery-ui-1.11.4.custom-DarkHive/jquery-ui.min.js"></script>
   <link rel="stylesheet" href="//files.devpgsv.com/libs/jquery-ui-1.11.4.custom-DarkHive/jquery-ui.css">
   <script src="../js/datepicker-es.js"></script>
   <script src="//cdn.datatables.net/1.10.10/js/jquery.dataTables.min.js"></script>
   <script src="//cdn.datatables.net/1.10.10/js/dataTables.jqueryui.min.js"></script>
   <style>
      body {
         background-image: url("https://files.devpgsv.com/public/img/whatsapp-green-wallpapers.png");
         background-color: #464646;
         color: #ffffff;
         padding: 0;
         margin: 0;
      }

      #loginbox {
         margin-top: 150px;
         margin-left: auto;
         margin-right: auto;
         width: 350px;
         text-align: center;
         border-radius: 20px;
         background-color: rgba(255, 47, 47, 0.3);
         padding:20px 40px 20px 40px;
         -webkit-transition: background-color 1s, border-radius 1s;
         transition: background-color 1s, border-radius 1s;
      }

      #loginbox:hover {
         background-color: rgba(0, 187, 255, 0.8);
         border-radius: 50px;
      }
      
      #topBar {
         position: fixed;
         top: 0;
         width: 100%;
         height: 50px;
         background: #959595;
         background: -moz-linear-gradient(top, #959595 0%, #0d0d0d 46%, #010101 50%, #0a0a0a 53%, #4e4e4e 76%, #383838 87%, #1b1b1b 100%);
         background: -webkit-linear-gradient(top, #959595 0%,#0d0d0d 46%,#010101 50%,#0a0a0a 53%,#4e4e4e 76%,#383838 87%,#1b1b1b 100%);
         background: linear-gradient(to bottom, #959595 0%,#0d0d0d 46%,#010101 50%,#0a0a0a 53%,#4e4e4e 76%,#383838 87%,#1b1b1b 100%);
         filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#959595', endColorstr='#1b1b1b',GradientType=0 );
         box-shadow: 0px 0px 40px white;
      }
      
      body:before {
         display:block;
         z-index: -100000 !important;
         height: 60px;
         content: "";
      }
      
      .dataTable {
         color: black;
      }
      
      .adminIcon {
         cursor: pointer;
      }

      .infolink {
         cursor: help;
      }

      .ui-autocomplete, .ui-selectmenu-open{
         z-index: 1000010;
      }

      .ui-datepicker {
         z-index: 1000011 !important;
      }

      .ui-selectmenu-menu > ul {
         height: 200px;
      }

      .ui-dialog {
         z-index: 101 !important;
      }
   </style>
   <script>
      $(document).ready(function(){
         $(document).tooltip({
            items: "[data-tooltip], [title]",
            content: function() {
               var element = $( this );
               if (element.is( "[title]")) {
                  return $(this).attr('title');
               }
               if (element.is( "[data-tooltip]")) {
                  return this.dataset.tooltip;
               }
            }
         });
         $( "#tabs" ).tabs();
         $(".custom-jui-button")
            .button()
            .css({
               'margin': '5px'
            });
         $('.custom-jui-textbox')
            .button()
            .css({
               'font' : 'inherit',
               'color' : 'inherit',
               'text-align' : 'left',
               'outline' : 'none',
               'cursor' : 'text',
               'margin': '5px'
         });
         $('#flights').dataTable({
            //'iDisplayLength': 100,
            "order": [[ 0, "asc" ]],
            "aoColumnDefs": [
               {
                  "bSortable": false,
                  "aTargets": [3, 4, 5]
               },
               {
                  "searchable": false,
                  "targets": [3, 4, 5]
               }
            ]
         });
         $('#myflights').dataTable({
            //'iDisplayLength': 100,
            "order": [[ 0, "asc" ]],
            "aoColumnDefs": [
               {
                  "bSortable": false,
                  "aTargets": [3, 4, 5]
               },
               {
                  "searchable": false,
                  "targets": [3, 4, 5]
               }
            ]
         });
         $('#airports').dataTable({
            'iDisplayLength': 100,
            "order": [[ 0, "asc" ]],
            "aoColumnDefs": [
               {
                  "bSortable": false,
                  "aTargets": [3]
               },
               {
                  "searchable": false,
                  "targets": [3]
               }
            ]
         });
         
         $(".buyFlight").click(function(){
            var currentFlight = this;
            var currentFlightSeats = JSON.parse(currentFlight.dataset.flightSeats);
            var popup = $('<div title="Buying Flight!">Flight #' + currentFlight.dataset.flightId + '\
               <input readonly="readonly" value="' + currentFlight.dataset.flightOriginName + ' -> ' + currentFlight.dataset.flightDestinationName + '" class="custom-jui-textbox"><br>\
               <form id="buyFlight_form" method="post"><fieldset>\
                  <select name="seat" id="buyFlight_form_select_seat" class="custom-jui-select" required><option disabled selected> -- Select a seat -- </option>\
                  </select> <label for="buyFlight_form_select_seat">Seat</label><br>\
                  <input type="hidden" name="id" value="'+currentFlight.dataset.flightId+'">\
               </fieldset></form>\
               <p id="popupMsg">...</p>\
               </div>');
            $.each(currentFlightSeats, function (i, item) {
               if (item !== false) {
                  console.log(item, <?php echo (isset($user) ? $user['id'] : "-1"); ?>);
                  if (item === <?php echo (isset($user) ? $user['id'] : "-1"); ?>) {
                     popup.find("form").find('#buyFlight_form_select_seat').append($('<option>', {
                        value: i,
                        text: i + " (I think I have bought this one!)",
                        disabled: true
                     }));
                  } else {
                     popup.find("form").find('#buyFlight_form_select_seat').append($('<option>', {
                        value: i,
                        text: i,
                        disabled: true
                     }));
                  }
                  
               } else {
                  popup.find("form").find('#buyFlight_form_select_seat').append($('<option>', {
                     value: i,
                     text: i
                  }));
               }
               
            });
            popup.find("form").find('select.custom-jui-select')
               .css({
                  'width': '400px'
               })
               .selectmenu();
            popup.find('.custom-jui-textbox')
               .button()
               .css({
                  'font' : 'inherit',
                  'color' : '#eeeeee',
                  'text-align' : 'left',
                  'outline' : 'none',
                  'cursor' : 'text',
                  'margin': '5px'
            });
            popup.dialog({
               modal: true,
               width: 600,
               closeOnEscape: true,
               buttons: {
                  "Buy": function() {
                     if (executePayment()) {
                        console.log("You paid :)");
                        sendAjaxRequest("ajax.php", "buyFlight", $("#buyFlight_form").serialize(), {'dialog':this, 'popup':popup},function(data, cb_data){
                           console.log(data);
                           if (data["status"] === "ok") {
                              $(cb_data['popup']).animate({backgroundColor: "rgb(0, 255, 0, 0.3)"},1000);
                              $(cb_data['popup']).find("#popupMsg").text(data["msg"]);
                              $(cb_data['dialog']).dialog('option', 'hide', 'fold');
                              window.setTimeout(function() {$(cb_data['dialog']).dialog("close");}, 2000);
                              window.setTimeout(function() {location.reload();}, 2750);
                           } else if (data["status"] === "error") {
                              $(cb_data['popup']).animate({backgroundColor: "rgb(255, 0, 0, 0.3)"},1000);
                              $(cb_data['popup']).find("#popupMsg").text(data["msg"]);
                              window.setTimeout(function() {popup.animate({backgroundColor: "rgba(0, 0, 0, 0)"},1000);}, 2000);
                           }
                        });
                     } else {
                        popup.animate({backgroundColor: "rgb(255, 0, 0, 0.3)"},1000);
                        $(popup).find("#popupMsg").text("You have to pay if you want to buy this.... ");
                        window.setTimeout(function() {popup.animate({backgroundColor: "rgba(0, 0, 0, 0)"},1000);}, 2000);
                     }
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
         
         
         $(".cancelFlight").click(function(){
            var currentFlight = this;
            var currentFlightSeatsBuyed = JSON.parse(currentFlight.dataset.flightSeatsBuyed);
            var popup = $('<div title="Cancel Flight!">Flight #' + currentFlight.dataset.flightId + '<br>\
               <form id="cancelFlight_form" method="post"><fieldset>\
                  <select name="seat" id="cancelFlight_form_select_seat" class="custom-jui-select" required><option disabled selected> -- Select a seat -- </option>\
                  </select> <label for="cancelFlight_form_select_seat">Seat</label><br>\
                  <input type="hidden" name="id" value="'+currentFlight.dataset.flightId+'">\
               </fieldset></form>\
               <p id="popupMsg">...</p>\
               </div>');
            $.each(currentFlightSeatsBuyed, function (i, item) {
               console.log(item);
               popup.find("form").find('#cancelFlight_form_select_seat').append($('<option>', {
                  value: item,
                  text: item
               }));
            });
            popup.find("form").find('select.custom-jui-select')
               .css({
                  'width': '400px'
               })
               .selectmenu();
            popup.dialog({
               modal: true,
               width: 600,
               closeOnEscape: true,
               buttons: {
                  "Cancel Flight": function(){
                     sendAjaxRequest("ajax.php", "cancelFlight", $("#cancelFlight_form").serialize(), {'dialog':this, 'popup':popup},function(data, cb_data){
                        console.log(data);
                        if (data["status"] === "ok") {
                           $(cb_data['popup']).animate({backgroundColor: "rgb(0, 255, 0, 0.3)"},1000);
                           $(cb_data['popup']).find("#popupMsg").text(data["msg"]);
                           $(cb_data['dialog']).dialog('option', 'hide', 'fold');
                           window.setTimeout(function() {$(cb_data['dialog']).dialog("close");}, 2000);
                           window.setTimeout(function() {location.reload();}, 2750);
                        } else if (data["status"] === "error") {
                           $(cb_data['popup']).animate({backgroundColor: "rgb(255, 0, 0, 0.3)"},1000);
                           $(cb_data['popup']).find("#popupMsg").text(data["msg"]);
                           window.setTimeout(function() {popup.animate({backgroundColor: "rgba(0, 0, 0, 0)"},1000);}, 2000);
                        }
                     });
                  },
                  "Don't cancel": function(){
                     $(this).dialog('option', 'hide', 'fade');
                     $(this).dialog("close");
                  }
               },
               show: "fold",
               hide: "scale"
            });
         });
      });
      
      function executePayment(cb_data, cb) {
        return confirm("Pay?");
      }
      
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
   <?php
   if (!$_SESSION['loggedin']) {
      ?>
      <div id="loginbox">
         <form action="?login" method="POST">
            <fieldset style="border: none;">
               <input type="text" class="custom-jui-textbox" name="username" id="loginform_username" placeholder="Username" title="Username" autocomplete="off">
               <br><input type="password" class="custom-jui-textbox" name="password" id="loginform_password" placeholder="Password" title="Password">
               <br><input type="submit" id="loginButton" class="custom-jui-button" value="Login" style="padding:20px 40px 20px 40px;">
            </fieldset>
         </form>
      </div>
      <script>
         $(document).ready(function(){
            $(document).tooltip();
            
         });
      </script>
      <?php
   } else {
      ?>
      <div id="topBar">
         <div style="position: absolute;right: 20px;">
            <span style="margin-right: 20px">
               Hi 
               <i style="color: #26b3f7">
                  <?php echo $user['username']; ?>
               </i>!
            </span>
            <a href="?logout" class="custom-jui-button">Logout</a>
         </div>
      </div>
      <div id="tabs">
         <ul>
            <li><a href="#tabs-flights">Flights (#<?php echo count($flights)-1; ?>)</a></li>
            <li><a href="#tabs-airports">Airports (#<?php echo count($airports)-1; ?>)</a></li>
            <li><a href="#tabs-myflights">My Flights (#<?php echo count($user['buyedFlights']); ?>)</a></li>
         </ul>
         <div id="tabs-flights">
            <h3>Flights (#<?php echo count($flights)-1; ?>)</h3> 
            <table id="flights" class="display" cellspacing="0" width="100%">
               <thead>
                  <tr>
                     <th>ID</th>
                     <th>Origin</th>
                     <th>Destination</th>
                     <th>Departure</th>
                     <th>Seats</th>
                     <th>Actions</th>
                  </tr>
               </thead>
               <tfoot>
                  <tr>
                     <th>ID</th>
                     <th>Origin</th>
                     <th>Destination</th>
                     <th>Departure</th>
                     <th>Seats</th>
                     <th>Actions</th>
                  </tr>
               </tfoot>
               <tbody>
                  <?php
                  foreach ($flights as $flight) {
                     $availableSeats = 0;
                     $flight['seats'] = json_decode($flight['seats'], true);
                     foreach ($flight['seats'] as $seat) {
                        if ($seat === false) $availableSeats++;
                     }
                     if ($flight['id'] == 0) continue;
                     echo '<tr>'
                              . '<td><p>'. $flight['id'] ."</p></td>\n"
                              . '<td><p class="infolink" data-tooltip="[Id: '.$flight['origin']."]<br/>". getAirportNameById($flight['origin'], $airports) ."\n(".getAirportCountryById($flight['origin'], $airports).')">'.getAirportNameById($flight['origin'], $airports).'</p></td>'
                              . '<td><p class="infolink" data-tooltip="[Id: '.$flight['destination']."]<br/>". getAirportNameById($flight['destination'], $airports) ."\n(".getAirportCountryById($flight['destination'], $airports).')">'.getAirportNameById($flight['destination'], $airports).'</p></td>'
                              . '<td>'. date("d/m/Y\<br> H:i", $flight['departure_timestamp']) ."</td>\n"
                              . '<td>'. $availableSeats .' / '.count($flight['seats'])."</td>\n"
                              . '<td style="text-align:center;">';
                                 echo '<img src="../img/buy_icon.png" class="buyFlight adminIcon" title="Buy flight" data-flight-id="'.$flight['id'].'" data-flight-origin-name='.getAirportNameById($flight['origin'], $airports).' data-flight-destination-name='.getAirportNameById($flight['destination'], $airports).' data-flight-date="'.date("d/m/Y", $flight['departure_timestamp']).'" data-flight-time="'.date("H:i", $flight['departure_timestamp']).'" style="margin-right: 25px" data-flight-seats="'.htmlspecialchars(json_encode($flight['seats'])).'">';
                                 if (isset($user['buyedFlights'][$flight['id']])) echo '<img src="../img/cancel_icon.png" class="cancelFlight adminIcon" title="Cancel flight" data-flight-id="'.$flight['id'].'" data-flight-origin='.$flight['origin'].' data-flight-destination='.$flight['destination'].' data-flight-date="'.date("d/m/Y", $flight['departure_timestamp']).'" data-flight-time="'.date("H:i", $flight['departure_timestamp']).'" data-flight-seats-buyed="'.htmlspecialchars(json_encode($user['buyedFlights'][$flight['id']])).'">';
                                 echo "</td>\n"
                           . '</tr>'."\n\n";
                  }
                  ?>
               </tbody>
            </table>
         </div>
         <div id="tabs-airports">
            <h3>Airports (#<?php echo count($airports)-1; ?>)</h3>
            <table id="airports" class="display" cellspacing="0" width="100%">
               <thead>
                  <tr>
                     <th>ID</th>
                     <th>Country</th>
                     <th>Name</th>
                     <th>Actions</th>
                  </tr>
               </thead>
               <tfoot>
                  <tr>
                     <th>ID</th>
                     <th>Country</th>
                     <th>Name</th>
                     <th>Actions</th>
                  </tr>
               </tfoot>
               <tbody>
                  <?php
                  foreach ($airports as $airport) {
                     if ($airport['id'] == 0) continue;
                     echo '<tr>'
                              . '<td><a name="airport-'. $airport['id'] .'"></a>'. $airport['id'] ."</td>\n"
                              . '<td>'. $airport['country'] ."</td>\n"
                              . '<td>'. $airport['displayname'] ."</td>\n"
                              . '<td style="text-align:center;">'
                                 . "</td>\n"
                           . "</tr>\n\n";
                  }
                  ?>
               </tbody>
            </table>
         </div>
         <div id="tabs-myflights">
            <h3>My Flights (#<?php echo count($user['buyedFlights']); ?>)</h3> 
            <table id="myflights" class="display" cellspacing="0" width="100%">
               <thead>
                  <tr>
                     <th>ID</th>
                     <th>Origin</th>
                     <th>Destination</th>
                     <th>Departure</th>
                     <th>Seats</th>
                     <th>Actions</th>
                  </tr>
               </thead>
               <tfoot>
                  <tr>
                     <th>ID</th>
                     <th>Origin</th>
                     <th>Destination</th>
                     <th>Departure</th>
                     <th>Seats</th>
                     <th>Actions</th>
                  </tr>
               </tfoot>
               <tbody>
                  <?php
                  foreach ($user['buyedFlights'] as $flightId => $seatsBuyed) {
                     $flight = $flights[$flightId];
                     $flight['seats'] = json_decode($flight['seats'], true);
                     if ($flight['id'] == 0) ;//continue;
                     echo '<tr>'
                              . '<td><p>'. $flight['id'] ."</p></td>\n"
                              . '<td><p class="infolink" data-tooltip="[Id: '.$flight['origin']."]<br/>". getAirportNameById($flight['origin'], $airports) ."\n(".getAirportCountryById($flight['origin'], $airports).')">'.getAirportNameById($flight['origin'], $airports).'</p></td>'
                              . '<td><p class="infolink" data-tooltip="[Id: '.$flight['destination']."]<br/>". getAirportNameById($flight['destination'], $airports) ."\n(".getAirportCountryById($flight['destination'], $airports).')">'.getAirportNameById($flight['destination'], $airports).'</p></td>'
                              . '<td>'. date("d/m/Y\<br> H:i", $flight['departure_timestamp']) ."</td>\n"
                              . '<td>'. implode(', ', $seatsBuyed) .' (#'.count($seatsBuyed).")</td>\n"
                              . '<td style="text-align:center;">';
                                 echo '<img src="../img/buy_icon.png" class="buyFlight adminIcon" title="Buy flight" data-flight-id="'.$flight['id'].'" data-flight-origin='.$flight['origin'].' data-flight-destination='.$flight['destination'].' data-flight-origin-name='.getAirportNameById($flight['origin'], $airports).' data-flight-destination-name='.getAirportNameById($flight['destination'], $airports).' data-flight-date="'.date("d/m/Y", $flight['departure_timestamp']).'" data-flight-time="'.date("H:i", $flight['departure_timestamp']).'" style="margin-right: 25px" data-flight-seats="'.htmlspecialchars(json_encode($flight['seats'])).'">';
                                 if (isset($user['buyedFlights'][$flight['id']])) echo '<img src="../img/cancel_icon.png" class="cancelFlight adminIcon" title="Cancel flight" data-flight-id="'.$flight['id'].'" data-flight-origin='.$flight['origin'].' data-flight-destination='.$flight['destination'].' data-flight-date="'.date("d/m/Y", $flight['departure_timestamp']).'" data-flight-time="'.date("H:i", $flight['departure_timestamp']).'" data-flight-seats-buyed="'.htmlspecialchars(json_encode($seatsBuyed)).'">';
                                 echo "</td>\n"
                           . '</tr>'."\n\n";
                  }
                  ?>
               </tbody>
            </table>
         </div>
      </div>
      
      <?php
   }
   ?>
</body>
</html>
