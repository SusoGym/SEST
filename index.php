<?php

session_start();

require "ChromePhp.php"; // debugging
require "class.connect.php";
require "class.user.php";
require "class.controller.php";
require "class.model.php";
require "class.view.php";
// ChromePhp::setEnabled(false);  // disable debugging

$input = array_merge($_GET, $_POST);
$control = new Controller($input);

?>
