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
$appId = $config['appId'];
?>   
<!DOCTYPE html>
<html xmlns:og="http://ogp.me/ns#"
      xmlns:fb="https://www.facebook.com/2008/fbml">
  <head>
    <title>Facemap</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta charset="UTF-8">
    <meta property="og:title" content="Facemap"/>
    <meta property="og:type" content="movie"/>
    <meta property="og:url" content="http://apps.facebook.com/where_friends"/>
    <meta property="og:image" content="http://ia.media-imdb.com/rock.jpg"/>
    <meta property="og:site_name" content="IMDb"/>
    <meta property='fb:app_id' content='$appId' />
    <meta property="og:description"
          content="A group of U.S. Marines, under command of
                   a renegade general, take over Alcatraz and
                   threaten San Francisco Bay with biological
                   weapons."/>
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
                $("#input_address").html(status);
            }
        });
    }
    </script>
  </head>
  <body>
        <!--required by facebook js api-->
        <div id="fb-root"></div>
        <script>(function(d, s, id) {
          var js, fjs = d.getElementsByTagName(s)[0];
          if (d.getElementById(id)) {return;}
          js = d.createElement(s); js.id = id;
          js.src = "//connect.facebook.net/en_GB/all.js#xfbml=1";
          fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));</script>
        
        <div id="menu">
            <div id="share_items">
                <div class="fb-like" data-send="false" data-layout="button_count" data-width="450" data-show-faces="false" data-action="recommend"></div>
            </div>
            <div id="menu_items">
                <div class="menu_item last_menu_item" id="menu_rank" align="center" onclick="showHelp()">Help</div>
                <!--<div class="menu_item" id="menu_liked" align="center" >Privacy Setting</div>-->
                <div class="menu_item " id="menu_main" align="center" onclick="showChangeLocation()">Change Location</div>
            </div>
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
          <div id="help_div">
              <h3>Help</h3>
              <p>Purpose:</p>
              <p>Due to certain reasons, some people need to move from place to place, thus losing close contact with some friends. Social network site doesn't remedy this situation as it doesn't connect people physically. 
                  This app is meant compensate this by tracking your friends' current living place, such that you can discover some old friends near your new place. Just call them out to have a drink, or play basketball at the community club. You won't feel lonely or isolated now.
                  Use this app to update your own living place after moving. It also may introduce new friends to you.
              
              </p>
              <p>Simple to use.</p>
              <p>Just enter your living location to start.After that you can see your friends face on the map if they are using the App.</p>
              <p>To invite more friends, use the Recommend Button on Top left.</p>
              <p>If you have any feedback, please email to zen1986@gmail.com</p>
              <span onclick="hideHelp()">close</span>
          </div>
          
          <div id="map_canvas"></div>
          <div id="friends_pane"></div>
      </div>
      <script>
          function showChangeLocation() {
              $("#change_location").animate({"top": 0});
          }
          
          function hideChangeLocation() {
              $("#change_location").animate({"top": -120});
          }
          
          function showHelp() {
              $("#help_div").animate({"top": 0});
          }
          
          function hideHelp() {
              $("#help_div").animate({"top": -420});
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
                showHelp();
          <?php endif; ?>
      </script>
  </body>
</html>
