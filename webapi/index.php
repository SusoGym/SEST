<?php

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
/* Debug Classes */
require "../ChromePhp.php"; // debugging - but has never been used by me!
//header('Content-Type: application/json;charset=utf-8');
/* Utilities*/
require "../base/class.superutility.php";
require "../class.user.php";
include("class.controller.php");
include("../class.connect.php");
include("../class.model.php");
include("class.webapimodel.php");

$data = array_merge($_POST,$_GET);
new Controller($data);

?>