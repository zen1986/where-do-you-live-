<?php
require_once '../fb_sdk/facebook.php';
require_once '../inc/utils.php';
require_once '../inc/appinfo.php';
require_once '../inc/db.php';

$fb = new Facebook($config);
$user = $fb->getUser();

$friends = $fb->api("/$user/friends");
$friends = processFriends($friends);

$location = "";
$qa = "";
$pa = "";
$fid = "";
if (count($friends)>0) {
    foreach ($friends as $fid=>$name) {
        $user = getUser($fid);
        if ($user) {
            $location = $user->location;
            $qa = $user->qa;
            $pa = $user->pa;
            echo "<div location=\"$location\" qa='$qa' pa='$pa' id='$fid' name='$name' class='friend_name'>$name</div>";
        }
    }
}
else {
    echo 'none';
}

//for ($i=0;$i<200;$i++) {
//    getUser($fid);
//    echo "<div location=\"$location\" qa='".($qa+$i)."' pa='$pa' fid='$fid'>$name</div>";
//}

//$gm_api="http://maps.googleapis.com/maps/api/geocode/json?address=".  urlencode("singapore")."&sensor=false";
//$ret=file_get_contents($gm_api);
//debug($ret);
?>