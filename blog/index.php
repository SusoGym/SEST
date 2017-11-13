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
// $data = array("console" => ""); //fixme: remove if no longer testing
$data = array_merge($data, $_SESSION, $_GET, $_POST);

if (isset($data['destroy'])) {
    session_destroy();
    /*foreach ( $_COOKIE as $key => $value ) {
      setcookie( $key, $value, time()-1, '/' );
    }*/
    // header('Location: .');
}

SuperUtility::handleDebug($data);
SuperUtility::setExceptionHandler($data);

\ChromePhp::info("Input: " . json_encode($data));

$controller = new Controller($data);
$controller->start("index.html");
