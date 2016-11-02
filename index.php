<?php

session_start();

require "ChromePhp.php"; // debugging
require "class.connect.php";
require "class.user.php";
require "class.controller.php";
require "class.model.php";
require "class.view.php";
// ChromePhp::setEnabled(false);  // disable debugging
enableCustomErrorHandler();

date_default_timezone_set('Europe/Berlin'); // if not corretly set in php.ini

$input = array_merge($_GET, $_POST);
$control = new Controller($input);


/**
 * This function will throw Exceptions instead of warnings (better to debug)
 */
function enableCustomErrorHandler()
{
    set_error_handler(function ($errno, $errstr, $errfile, $errline) {
        // error was suppressed with the @-operator
        if (0 === error_reporting()) {
            return false;
        }

        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    });
}

?>
