<?php namespace administrator;


session_start();

require "ChromePhp.php"; // debugging  -> this way it's modular else we could use ../ChromPhp.php
require "administrator.connect.class.php";
require "administrator.controller.class.php";
require "administrator.model.class.php";
require "administrator.view.class.php";
require "administrator.filehandler.class.php";
require "administrator.user.class.php";
enableCustomErrorHandler();


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

        throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
    });
}

?>
