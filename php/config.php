<?php
include 'head.php';
include 'nav.php';
$db = new PDO('sqlite:db/movies.db') or die("fail to connect db");

$ugly_url = "$_SERVER[REQUEST_URI]";
$url = (explode("/", $ugly_url));

getHead(ucfirst($url[1]));
getNav(ucfirst($url[1]));

?>
           
