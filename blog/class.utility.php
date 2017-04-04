<?php namespace blog;
class Utility {
    
    /**
     * @var string defines where templates are located
     */
    static $TEMPLATE_DIR = "templates";
    static $DEFAULT_TEMPLATE = "main";
    
    /**
     * This function will throw Exceptions instead of warnings (better to debug)
     */
    static function enableCustomErrorHandler() {
        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            // error was suppressed with the @-operator
            if (0 === error_reporting()) {
                return false;
            }
            
            throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
        });
    }
    
    /**
     * This function will handle whether or not to print debug messages
     *
     * @param $data array
     */
    static function handleDebug($data) {
        $enable = true;
        \ChromePhp::setEnabled($enable);
        ini_set("display_errors", true);
        if ($enable)
            self::enableCustomErrorHandler();
    }
    
    /**
     * Returns false if key not existent else return value in array
     *
     * @param $arr array
     * @param $key string
     * @return bool
     */
    static function getExistentAndValue($arr, $key) {
        return isset($arr[$key]) ? ($arr[$key] == "" ? true : $arr[$key] == "true" ? true : false) : false;
    }
    
    /**
     * Return content to key in array, if not existent return fallback
     * @param $arr array
     * @param $key string
     * @param $fallback mixed
     * @return mixed
     */
    static function getOrFallBack($arr, $key, $fallback)
    {
        return isset($arr[$key]) ? $arr[$key] : $fallback;
    }
    
    /**
     * Displays specific template, if template not existent display fallback
     *
     * @param $template string
     * @param null $fallBack string
     */
    static function displayTemplate($template, $fallBack = null) {
        $templateFile = self::$TEMPLATE_DIR . DIRECTORY_SEPARATOR . $template . '.php';
        $exists = file_exists($templateFile);
        
        if (!$exists && $fallBack != null) {
            $templateFile = self::$TEMPLATE_DIR . DIRECTORY_SEPARATOR . $template . '.php';
            $exists = file_exists($templateFile);
        }
        
        if (!$exists) {
            die("Unable to locate template '$templateFile'!");
        }
        
        \ChromePhp::info("Displaying '$templateFile'");
        /** @noinspection PhpIncludeInspection */
        include($templateFile);
        
    }
}
