<?php

session_start();
require "ChromePhp.php"; // debugging
require "administrator.connect.class.php";
require "administrator.controller.class.php";
require "administrator.model.class.php";
require "administrator.view.class.php";
require "administrator.filehandler.class.php";

$input = array_merge($_GET, $_POST);
$control = new Controller($input);

?>
