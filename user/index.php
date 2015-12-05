<?php
ini_set('display_errors','1'); error_reporting(E_ALL);
header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('Europe/Madrid');
require('../api/db.php');
require('../api/utils.php');
session_start();

if (!isset($_SESSION['loggedin']) || empty($_SESSION['loggedin'])) $_SESSION['loggedin'] = false;
if (isset($_GET['login']) && isset($_POST['username']) && !empty($_POST['username']) && isset($_POST['password']) && !empty($_POST['password'])) {
   $stmt = $db->prepare("SELECT * FROM users WHERE username=:username");
   $stmt->bindValue(':username', $_POST['username'], PDO::PARAM_STR);
   $stmt->execute();
   $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
   if (count($users) == 1) {
      // Check password
      if (true) {
         $_SESSION['loggedin'] = true;
         $_SESSION['userid'] = $users[0]['id'];
      }
   }
}
if (isset($_GET['logout'])) $_SESSION['loggedin'] = false;

if ($_SESSION['loggedin']) {
   if (!isset($_SESSION['userid']) || empty($_SESSION['userid'])) {$_SESSION['loggedin'] = false; exit();}
   $stmt = $db->prepare("SELECT * FROM users WHERE id=:id");
   $stmt->bindValue(':id', $_SESSION['userid'], PDO::PARAM_INT);
   $stmt->execute();
   $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
   if (count($users) !== 1) {$_SESSION['loggedin'] = false; exit();}
   $user = $users[0];
   unset($users);
}


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

?><!DOCTYPE html>
<html>
<head>
   <title>Flight Finder!</title>
   <meta charset="UTF-8">
   <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
   <link rel="icon" href="favicon.ico" type="image/x-icon">

   <link rel="stylesheet" href="https://cdn.datatables.net/1.10.10/css/dataTables.jqueryui.min.css">
   <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
   <script src="http://files.devpgsv.com/libs/jquery-ui-1.11.4.custom-DarkHive/jquery-ui.min.js"></script>
   <link rel="stylesheet" href="http://files.devpgsv.com/libs/jquery-ui-1.11.4.custom-DarkHive/jquery-ui.css">
   <script src="../js/datepicker-es.js"></script>
   <script src="https://cdn.datatables.net/1.10.10/js/jquery.dataTables.min.js"></script>
   <script src="https://cdn.datatables.net/1.10.10/js/dataTables.jqueryui.min.js"></script>
   <style>
      body {
         background-image: url("http://static.tumblr.com/c2b3cd97fa4ae874a2b115cc5fe211b7/9tzlnzy/jLen2lq4u/tumblr_static_3602651-756501-seamless-black-wallpaper-pattern.jpg");
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
         background-color: rgba(255, 255, 255, 0.4);
         padding:20px 40px 20px 40px;
         -webkit-transition: background-color 1s, border-radius 1s;
         transition: background-color 1s, border-radius 1s;
      }

      #loginbox:hover {
         background-color: rgba(255, 255, 255, 0.7);
         border-radius: 40px;
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
            'iDisplayLength': 100,
            "order": [[ 0, "asc" ]],
            "aoColumnDefs": [{
                  "bSortable": false,
                  "aTargets": [4, 5]
               }]
         }).css({
            'color' : 'black'
         });
         $('#airports').dataTable({
            'iDisplayLength': 100,
            "order": [[ 0, "asc" ]],
            "aoColumnDefs": [{
                  "bSortable": false,
                  "aTargets": [3]
               }]
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
   <?php
   if (!$_SESSION['loggedin']) {
      ?>
      <div id="loginbox">
         <fieldset style="border: none;">
            <input type="text" class="custom-jui-textbox" name="username" id="loginform_username" placeholder="Username" title="Username" autocomplete="off">
            <br><input type="password" class="custom-jui-textbox" name="password" id="loginform_password" placeholder="Password" title="Password">
            <br><input type="submit" id="loginButton" class="custom-jui-button" value="Login" style="padding:20px 40px 20px 40px;">
         </fieldset>
      </div>
      <script>
         $(document).ready(function(){
            $(document).tooltip();
            $("#loginButton").click(function(){
               $('<form action="?login" method="POST"></form>')
                  .append('<input type="text" name="username" value="' + $("#loginform_username").val() + '" />')
                  .append('<input type="password" name="password" value="' + $("#loginform_password").val() + '" />')
                  .submit();
               console.log("a");
            });
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
            <li><a href="#tabs-myflights">My Flights</a></li>
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
                     if ($flight['id'] == 0) continue;
                     echo '<tr>'
                              . '<td><p>'. $flight['id'] ."</p></td>\n"
                              . '<td><p class="infolink" data-tooltip="[Id: '.$flight['origin']."]<br/>". getAirportNameById($flight['origin'], $airports) ."\n(".getAirportCountryById($flight['origin'], $airports).')">'.getAirportNameById($flight['origin'], $airports).'</p></td>'
                              . '<td><p class="infolink" data-tooltip="[Id: '.$flight['destination']."]<br/>". getAirportNameById($flight['destination'], $airports) ."\n(".getAirportCountryById($flight['destination'], $airports).')">'.getAirportNameById($flight['destination'], $airports).'</p></td>'
                              . '<td>'. date("d/m/Y\<br> H:i", $flight['departure_timestamp']) ."</td>\n"
                              . '<td>'. $flight['seats'] ."</td>\n"
                              . '<td style="text-align:center;">'
                                 . '<img src="../img/buy_icon.png" class="buyFlight adminIcon" title="Buy flight" data-flight-id="'.$flight['id'].'" data-flight-origin='.$flight['origin'].' data-flight-destination='.$flight['destination'].' data-flight-date="'.date("d/m/Y", $flight['departure_timestamp']).'" data-flight-time="'.date("H:i", $flight['departure_timestamp']).'">'
                                 . '<img src="../img/cancel_icon.png" style="margin-left:25px;" class="cancelFlight adminIcon" title="Cancel flight" data-flight-id="'.$flight['id'].'" data-flight-origin='.$flight['origin'].' data-flight-destination='.$flight['destination'].' data-flight-date="'.date("d/m/Y", $flight['departure_timestamp']).'" data-flight-time="'.date("H:i", $flight['departure_timestamp']).'">'
                                 . "</td>\n"
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
                                 . '<img src="../img/modify_icon.png" class="modifyAirport adminIcon" title="Modify Airport" data-airport-id="'.$airport['id'].'" data-airport-country="'.$airport['country'].'" data-airport-displayname="'.$airport['displayname'].'">'
                                 . '<img src="../img/delete_icon.png" class="deleteAirport adminIcon" title="Delete Airport" data-airport-id="'.$airport['id'].'" style="margin-left:25px;">'
                                 . "</td>\n"
                           . "</tr>\n\n";
                  }
                  ?>
               </tbody>
            </table>
            
            
            
            
            
            
         </div>
         <div id="tabs-myflights">
            <p>My Flights</p>
            <br>
            Not done yet....
         </div>
      </div>
      
      <?php
   }
   ?>
</body>
</html>
