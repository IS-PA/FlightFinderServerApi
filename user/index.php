<!DOCTYPE html>
<html>
   <head>
      <title>Flight Finder!</title>
      <meta charset="UTF-8">
      <!--<meta name="viewport" content="width=device-width, initial-scale=1.0">-->
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
            /*http://static.tumblr.com/c2b3cd97fa4ae874a2b115cc5fe211b7/9tzlnzy/jLen2lq4u/tumblr_static_3602651-756501-seamless-black-wallpaper-pattern.jpg*/
            /*http://www.myfreetextures.com/wp-content/uploads/2014/10/white-fur-texture.jpg*/
            background-color: #464646;
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
      </style>
   </head>
<body>
   <div id="loginbox">
      <fieldset style="border: none;">
         <input type="text" class="custom-jui-textbox" name="username" id="loginform_username" placeholder="Username" title="Username" autocomplete="off">
         <br><input type="password" class="custom-jui-textbox" name="password" id="loginform_password" placeholder="Password" title="Password">
         <br><input type="submit" class="custom-jui-button" value="Login" style="padding:20px 40px 20px 40px;">
      </fieldset>
   </div>
   <script>
      $(document).ready(function(){
         $(document).tooltip();
         $("input.custom-jui-button")
            .button()
            .css({
               'margin': '5px'
            })
            .click(function(e) {
               e.preventDefault();
            });
         $('input.custom-jui-textbox')
            .button()
            .css({
               'font' : 'inherit',
               'color' : 'inherit',
               'text-align' : 'left',
               'outline' : 'none',
               'cursor' : 'text',
               'margin': '5px'
         });
      });
   </script>
</body>
</html>
