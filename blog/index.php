<?php namespace blog;


use base\SuperUtility;

require("../base/dependencies.php");
require("class.dependencies.php");
require("class.post.php");
require("class.model.php");
require("class.user.php");
require("class.controller.php");

session_start();

date_default_timezone_set('Europe/Berlin');

$data = array();
$data = array_merge($data, $_SESSION, $_GET, $_POST);

if (isset($data['destroy'])) {
    session_destroy();
}

SuperUtility::handleDebug($data);
SuperUtility::setExceptionHandler($data);

\ChromePhp::info("Input: " . json_encode(array_merge($_GET, $_POST)));
\ChromePhp::info("Session: " . json_encode($_SESSION));

$controller = new Controller($data);
$controller->start("index.html");
