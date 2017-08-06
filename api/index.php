<?php
namespace api;

use base\FireBase;
use base\SuperUtility;

require("../base/dependencies.php");
require("class.model.php");
require("class.classes.php");
require("class.controller.php");

date_default_timezone_set('Europe/Berlin');

$data = array_merge($_GET, $_POST);


SuperUtility::handleDebug($data);
SuperUtility::setExceptionHandler($data);

\ChromePhp::info("Input: " . json_encode($data));

$controller = new Controller($data);

FireBase::fetchApiAccessKey();

$controller->start();