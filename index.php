<?php

    $input = array_merge($_GET, $_POST);
	$DEBUG = false || isset($input['debug']);
    $SQL_DEBUG = false || isset($input['sqldebug']);

    /* Debug Classes */
    require "ChromePhp.php"; // debugging
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

    if ($DEBUG) {
        ini_set("display_errors", true);
        View::$DEBUG = true;
        enableCustomErrorHandler();
    }

    date_default_timezone_set('Europe/Berlin'); // if not corretly set in php.ini

    /* Let's go! */
    session_start();

    if (isset($input['destroy'])) {
        session_destroy();
        header("Location: /");
    }


    $control = new Controller($input);

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
