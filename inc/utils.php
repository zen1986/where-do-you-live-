<?php

/*
 *  input: directory
 *  output: array(
 *                  'dir' => 'xxxxxxxx', 
 *                  'images' => array( ...., array(filename, imagesize array), ...)
 *          )
 */
function readPhotoDir($dir) {
    $ret = array();
    if (is_dir($dir)) {
        $ret['dir'] = $dir;
        if ($dh = opendir($dir)) {
            while (($file = readdir($dh)) !== false) {
                if (filetype($dir . $file)=='file') {
                    $ret['images'][] = array($file, getimagesize($dir.$file));
                }
            }
            closedir($dh);
        }
    }
    else {
        echo "it's not a dir";
    }
    return $ret;
}

function debug($var) {
    echo "<pre>";
    var_dump($var);
    echo "</pre>";
}

/*
 * post a feed
 * 
 */

function postFeed($user, $message, $accessToken) {

    $ch = curl_init("https://graph.facebook.com/$user/feed");
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt ($ch, CURLOPT_POSTFIELDS, "access_token=$accessToken&message=$message"); 
    return json_decode(curl_exec($ch), true);
}

function sendQuery($fql, $fb) {
    $fql_obj = array(
        'method'=>'fql.query',
        'query'=>$fql
    );
    return $fb->api($fql_obj);
}

function checkPermission($permission, $fb) {
    $fql = "SELECT $permission FROM permissions WHERE uid=me()";
    return sendQuery($fql, $fb);
}
//get locations
function getFbLocation($id, $fb) {
    $fql = "SELECT current_location FROM user WHERE uid=$id";
    $ret = sendQuery($fql, $fb);
    if ($ret[0]['current_location']!=null) {
        return $ret[0]['current_location']['name'];
    }else {
        return null;
    }
}

function processFriends($input) {
    $friends = array();
    foreach ($input['data'] as $friend) {
        $friends[$friend['id']] = $friend['name'];
    }
    return $friends;
}
?>
