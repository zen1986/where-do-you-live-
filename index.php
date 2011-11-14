<?php
require_once 'fb_sdk/facebook.php';
require_once 'inc/utils.php';
require_once 'inc/appinfo.php';
require_once 'inc/db.php';

$fb = new Facebook($config);
$user = $fb->getUser();
if ($user == 0) {
    $appdId=$config['appId'];
    $redirect="http://$domain";
    $url = "https://www.facebook.com/dialog/oauth?client_id=$appdId&redirect_uri=$redirect&scope=email,read_stream,publish_stream,manage_pages";
    header("location: $url");
    exit();
}         

$sql = "select * from location where uid=$user";
$ret = mysql_fetch_object(mysql_query($sql));
if (!$ret) {
    //system doesnot have current user's location
    //ask him to give location
} else {
    $location = $ret->location;
    $qa = $ret->qa;
    $pa = $ret->pa;
}

?>   
<!DOCTYPE html>
<html>
  <head>
    <title>Google Maps JavaScript API v3 Example: Map Simple</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta charset="UTF-8">
    <script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?sensor=false"></script>
    <script type="text/javascript" src="http://ajax.aspnetcdn.com/ajax/jQuery/jquery-1.6.4.min.js"></script>
    <link href="css/main.css" rel="stylesheet" type="text/css" />
    <script type="text/javascript">
      var map;
      var geocoder;
      var infoWin = new google.maps.InfoWindow();
      function initialize() {
        <?php if(isset($location)): ?>
        var pos = new google.maps.LatLng(<?php echo $pa.", ".$qa; ?>);
        <?php else: ?>
        var pos = new google.maps.LatLng(0,0);    
        <?php endif;?>
        geocoder = new google.maps.Geocoder();
        var myOptions = {
          zoom: 8,
          center: pos,
          mapTypeId: google.maps.MapTypeId.ROADMAP
        };
        map = new google.maps.Map(document.getElementById('map_canvas'),myOptions);
        <?php if(isset($location)): ?>
          var markerOption =  new google.maps.Marker({
            map: map,
            position: pos,
            icon: new google.maps.MarkerImage("http://graph.facebook.com/<?php echo $user;?>/picture")
          });
        <?php endif;?>
          
      }
      google.maps.event.addDomListener(window, 'load', initialize);
      
      
      function validateAddress() {
        var input = document.getElementById('location');
        var address = input.value;
        geocoder.geocode( { 'address': address}, function(results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                var a = results[0];
                var latlng = a.geometry.location;
                $("#input_address").html(a.formatted_address);
                $("#input_address").append("<input type='submit'value='Confirm'/>");
                $("#hidden_address").val(a.formatted_address);
                $("#hidden_p").val(latlng.Pa);
                $("#hidden_q").val(latlng.Qa);
            }
            else {
            }
        });
    }
    </script>
  </head>
  <body>
        <div id="menu">
            <div class="menu_item last_menu_item" id="menu_rank" align="center" onclick="showChangeLocation()">Help</div>
            <div class="menu_item" id="menu_liked" align="center" onclick="window.location = 'index.php?page=liked';">Privacy Setting</div>
            <div class="menu_item " id="menu_main" align="center" onclick="showChangeLocation()">Change Location</div>
        </div>
      <div id="content">
          <div id="change_location">
              <form method="post" action="pages/saveAddress.php">
                  <fieldset style="height:60px; margin-top: 20px; background-color: ghostwhite;">
                      <legend>
                          Where do you live now?
                      </legend>
                      <input type="text" size="50" name="location" id="location"/>
                      <button onclick="validateAddress();return false;">Submit</button>
                      <div id="confirmation">
                          <span id="input_address"></span>
                      </div>
                      <input type="hidden" id="hidden_address" name="hidden_address"/>
                      <input type="hidden" id="hidden_q" name="hidden_q"/>
                      <input type="hidden" id="hidden_p" name="hidden_p"/>
                  </fieldset>
              </form>
              <span onclick="hideChangeLocation()">close</span>
          </div>
          <div id="map_canvas"></div>
          <div id="friends_pane"></div>
      </div>
      <script>
          function showChangeLocation() {
              $("#change_location").animate({"top": 0});
          }
          
          function hideChangeLocation() {
              $("#change_location").animate({"top": -100});
          }
          <?php if (isset($location)): ?>
          $("#friends_pane").load("pages/loadFriends.php", function (r, t) {
              if (t == 'success') {
                    $("#friends_pane").append("<div location=\"<?php echo $location;?>\" qa='<?php echo $qa;?>' pa='<?php echo $pa;?>' id='<?php echo $user;?>' name='me' class='friend_name'>me</div>");
                    var f_divs = $("#friends_pane div");
                    for (var i=0;i<f_divs.length;i++) {
                        var div = f_divs[i];
                        var location =div.getAttribute('location');
                        var qa =div.getAttribute('qa');
                        var pa =div.getAttribute('pa');
                        var fid =div.getAttribute('id');
                        var fname =div.getAttribute('name');
                        var pos = new google.maps.LatLng(pa, qa);
                        var marker = new google.maps.Marker({
                            fid: fid,
                            fname: fname,   
                            location: location,
                            map: map,
                            position: pos,
                            icon: new google.maps.MarkerImage("http://graph.facebook.com/"+fid+"/picture")
                        });
                        google.maps.event.addListener(marker, 'click', function() {
                            var infoOp = {
                               content: "<div>"+this.fname+"</div><div>"+this.location+"</div>",
                               position: this.position
                            };
                            infoWin.setOptions(infoOp);
                            infoWin.open(map);
                        });
                        
                        $("#"+fid).click(function () {
                            var c = new google.maps.LatLng ($(this).attr('pa'), $(this).attr('qa'));
                            map.setCenter(c);
                            map.setZoom(16);
                            var infoOp = {
                               content: "<div>"+$(this).attr('name')+"</div><div>"+$(this).attr('location')+"</div>",
                               position: c
                            };
                            infoWin.setOptions(infoOp);
                            infoWin.open(map);
                        });
                    }
              }
          });
          <?php else: ?>
                showChangeLocation();
          <?php endif; ?>
      </script>
  </body>
</html>
