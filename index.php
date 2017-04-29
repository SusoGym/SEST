<?php

/* Let's go! */
session_start();


$input = array_merge($_GET, $_POST);

/* Debug Classes */
require "ChromePhp.php"; // debugging

$arr = setupDebug($input);
$DEBUG = false;//$arr[0];
$SQL_DEBUG = false;//$arr[1];

/* Utility Classes */
require "class.utility.php";
require "class.user.php";
/* Functional Classes */
require "class.connect.php";
require "class.controller.php";
require "class.model.php";
require "class.view.php";
require "class.termin.php";
require "class.coverLesson.php";

/* Settings */

\ChromePhp::setEnabled($DEBUG);
ChromePhp::setSQLDebug($SQL_DEBUG);

if ($DEBUG) {
    ini_set("display_errors", true);
    View::$DEBUG = true;
    enableCustomErrorHandler();
}

date_default_timezone_set('Europe/Berlin'); // if not corretly set in php.ini


if (isset($input['destroy'])) {
    session_destroy();
    header("Location: /");
}


$control = new Controller($input);

function setupDebug($input)
{
    $SQL_DEBUG = false;
    $DEBUG = false;

    if(isset($input['nodebug']))
    {
        unset($_SESSION['sqldebug']);
        unset($_SESSION['debug']);
        ChromePhp::info("Debug: 0 SQL: 0");
        return array(0, 0);
    }

    $SQL_DEBUG = isset($input['sqldebug']) || isset($_SESSION['sqldebug']);
    $DEBUG = isset($input['debug']) || isset($_SESSION['debug']);

    $_SESSION['sqldebug'] = $SQL_DEBUG;
    $_SESSION['debug'] = $DEBUG;

    ChromePhp::info("Debug: $DEBUG SQL: $SQL_DEBUG");
    if(!$DEBUG && $SQL_DEBUG)
        $DEBUG = true;

    return array($DEBUG, $SQL_DEBUG);
}

/**
 * This function will throw Exceptions instead of warnings (better to debug)
 */
function enableCustomErrorHandler() {
    set_error_handler(function ($errno, $errstr, $errfile, $errline) {
        // error was suppressed with the @-operator
        if (0 === error_reporting()) {
            return false;
        }

        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    });
}

?>
