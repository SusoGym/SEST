<?php namespace blog;

require("../ChromePhp.php");
require ("../class.connect.php");
require("class.model.php");
require("class.utility.php");
require("class.controller.php");

session_start();

$data = array("console" => "");
$data = array_merge($data, $_SESSION, $_GET, $_POST);

Utility::handleDebug($data);

\ChromePhp::info("Input: " . json_encode($data));

$controller = new Controller($data);
$controller->go();