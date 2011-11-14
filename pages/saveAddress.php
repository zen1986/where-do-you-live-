<?php
include_once "../inc/utils.php";
include_once "../inc/appinfo.php";
include_once "../fb_sdk/facebook.php";

if (isset($_REQUEST['hidden_address'])&&isset($_REQUEST['hidden_p'])&&isset($_REQUEST['hidden_q'])) {
    require_once '../inc/db.php';

    $fb = new Facebook($config);
    $location = $_REQUEST['hidden_address'];
    $uid = $fb->getUser();
    $pa = $_REQUEST['hidden_p'];
    $qa = $_REQUEST['hidden_q'];
    $location = str_replace("+", " ", $location);
    $sql = "REPLACE INTO `location` (`uid`, `location`, `qa`, `pa`) VALUES ($uid, \"$location\", $qa, $pa);";
    
    if (mysql_query($sql)) {
        header("location: http://$domain");
    } else {
        
    }
} 
?>