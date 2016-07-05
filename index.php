<?php

session_start();

require "class.controller.php";
require "class.model.php";
require "class.view.php";

$input = array_merge($_GET, $_POST);
$control = new Controller($input);

?>
