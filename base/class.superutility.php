<?php
namespace base;
class SuperUtility {
    
    /**
     * @var string defines where templates are located
     */
    static $TEMPLATE_DIR = "templates";
    static $DEFAULT_TEMPLATE = "main";
    static $errorless_exit = true;
    
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
        \ChromePhp::setSQLDebug($enable);
        ini_set("display_errors", true);
        if ($enable)
            self::enableCustomErrorHandler();
    }
    
    /**
     * Sets Custom Exception Handler
     *
     * @param $data User Input
     */
    static function setExceptionHandler($data) {
        $json = boolval(self::getExistentAndValue($data, "console"));
        
        if (!$json || self::getExistentAndValue($data, "fullException"))
            return;
        
        set_exception_handler(function ($exception) {
            
            /** @var $exception \Exception */
            $data = array("type" => "unknown", "message" => $exception->getMessage());
            
            if ($exception instanceof \MySQLException) {
                $data = $exception->getData();
            }
            
            $data = array("code" => 500, "message" => "Uncaught exception!", "payload" => $data);
            self::$errorless_exit = false;
            die(json_encode($data, JSON_PRETTY_PRINT));
            
        });
        
    }
    
    /**
     * Search trough array ignoring case, returns null if not existent else returns value
     *
     * @param $arr array to search trough
     * @param $key string key to search for
     *
     * @return mixed|null
     */
    static function getIgnoreCaseOrNull($arr, $key) {
        foreach ($arr as $k => $value) {
            if (strtolower($k) == strtolower($key)) {
                return $value;
            }
        }
        
        return null;
    }
    
    /**
     * Returns false if key not existent else return value in array
     *
     * @param $arr array
     * @param $key string
     *
     * @return bool
     */
    static function getExistentAndValue($arr, $key) {
        return isset($arr[$key]) ? ($arr[$key] == "" ? true : $arr[$key] == "true" ? true : false) : false;
    }
    
    /**
     * Return content to key in array, if not existent return fallback
     *
     * @param $arr      array
     * @param $key      string
     * @param $fallback mixed
     *
     * @return mixed
     */
    static function getOrFallBack($arr, $key, $fallback) {
        return isset($arr[$key]) ? $arr[$key] : $fallback;
    }
    
    /**
     * Displays specific template, if template not existent display fallback
     *
     * @param      $template string
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
    
    /** Verifies login-data via est-site
     *
     * @param $username
     * @param $pwd
     *
     * @return bool correct login data
     */
    static function verifyLogin($username, $pwd) {
        $url = "https://" . $_SERVER['HTTP_HOST'];
        /** @var resource $ch */
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); //fixme: ssl unsafe!
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array("login[mail]" => $username, "login[password]" => $pwd, "console" => true, "type" => "login")));
        
        $result = utf8_decode(curl_exec($ch));
        $length = strlen($result);
        
        $result = substr($result, 1, $length); // but why????
        
        return $result == "true";
    }
    
    /** Generates Auth-Token for specified login-data
     *
     * @param $username
     * @param $pwd
     *
     * @return string|null auth-token | null if invalid login-data
     */
    static function generateAuthToken($username, $pwd) {
        
        if (!self::verifyLogin($username, $pwd))
            return null;
        
        $user = Model::getInstance()->getUserByName($username);
        
        if ($user == null) {
            $user = Model::getInstance()->createUserByName($username);
        }
        
        $authToken = Model::getInstance()->getToken($user->getId());
        
        return $authToken;
    }
}
