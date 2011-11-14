<?php

$link = mysql_connect('localhost', 'root', 'password');
if (!$link) {
    die('Could not connect: ' . mysql_error());
}

mysql_select_db('where');


function getUser($id) {
    $sql = "select * from location where uid=$id";
    $ret = mysql_query($sql);
    return mysql_fetch_object($ret);
}
?>
