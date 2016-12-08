<?php namespace administrator;


    session_start();

    const DEBUG = true;
    const SQL_DEBUG = false;


    require "../ChromePhp.php"; // debugging
    require "../class.utility.php";
    require "../class.user.php";
    require "../class.connect.php";
    require "../class.controller.php";
    require "../class.model.php";
    require "../class.view.php";

    \Connection::$configFile = "../cfg.ini";

    require "administrator.controller.class.php";
    require "administrator.model.class.php";
    require "administrator.filehandler.class.php";

    \ChromePhp::setEnabled(DEBUG);

    if (DEBUG)
    {
        ini_set("display_errors", true);
        enableCustomErrorHandler();
    }

    $input = array_merge($_GET, $_POST);

    $control = new Controller($input);


    /**
     * This function will throw Exceptions instead of warnings (better to debug)
     */
    function enableCustomErrorHandler()
    {
        set_error_handler(function ($errno, $errstr, $errfile, $errline)
        {
            // error was suppressed with the @-operator
            if (0 === error_reporting())
            {
                return false;
            }

            throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
        });
    }

?>
