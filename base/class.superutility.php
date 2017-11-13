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
     * $this function will throw Exceptions instead of warnings (better to debug)
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
     * $this function will handle whether or not to print debug messages
     *
     * @param $data array
     */
    static function handleDebug($data) {
        $enableData = self::getIgnoreCaseOrNull($data, "debug");
        $enable = $enableData == null ? isset($data['debug']) : $enableData;
        
        $enableSQLData = self::getIgnoreCaseOrNull($data, "sqldebug");
        $enableSQL = $enableSQLData == null ? isset($data['sqldebug']) : $enableSQLData;
        
        \ChromePhp::setEnabled($enable);
        \ChromePhp::setSQLDebug($enableSQL);
        ini_set("display_errors", true);
        if ($enable || $enableSQL) {
            self::enableCustomErrorHandler();
        }
    }
    
    /**
     * Sets Custom Exception Handler
     *
     * @param $data array Input
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
        
        $suffix = strpos($template, ".html") === false;
        
        $templateFile = self::$TEMPLATE_DIR . DIRECTORY_SEPARATOR . $template . ($suffix ? '.php' : "");
        $exists = file_exists($templateFile);
        
        if (!$exists && $fallBack != null) {
            $templateFile = self::$TEMPLATE_DIR . DIRECTORY_SEPARATOR . $fallBack . ($suffix ? '.php' : "");
            $exists = file_exists($fallBack);
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
        $url = "https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
        
        $pos = strrpos($url, "/blog");
        
        if ($pos !== false) {
            $url = substr_replace($url, "", $pos, strlen($url));
        } else {
            $url = "https://" . $_SERVER['HTTP_HOST'];
        }
        
        $url .= "/index.php";
        
        /** @var resource $ch */
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array("login[mail]" => $username, "login[password]" => $pwd, "console" => true, "type" => "login")));
        
        $result = utf8_decode(curl_exec($ch));
        $length = strlen($result);
        
        $result = substr($result, 1, $length); // but why????
        
        \ChromePhp::info("Checking login integrity from " . $url . "... Result is: " . $result);
        
        return $result == "true";
    }
}

abstract class Printable implements \JsonSerializable {
    /**
     * @return array[String=>mixed]
     */
    public abstract function getData();
    
    /**
     * @return string
     */
    public abstract function getClassType();
    
    /* Functional stuff */
    /**
     * General __toString() override
     *
     * @return string
     */
    public function __toString() {
        return $this->getClassType() . ':' . json_encode($this->getData());
    }
    
    /**
     * General method to serialize to json
     *
     * @return array
     */
    public function jsonSerialize() {
        return array("type" => $this->getClassType(), "data" => $this->getData());
    }
    
}

/**
 * Class FireBase
 *
 * @package base
 * More details: https://firebase.google.com/docs/cloud-messaging/http-server-ref
 */
class FireBase {
    
    private static $API_ACCESS_KEY;
    
    public static function fetchApiAccessKey() {
        \ChromePhp::info(SuperModel::getInstance()->getConnection()->getIniParams());
        self::$API_ACCESS_KEY = SuperModel::getInstance()->getConnection()->getIniParams()['firebase_key'];
    }
    
    public static function setApiAccessKey($apiAccessKey) {
        self::$API_ACCESS_KEY = $apiAccessKey;
    }
    
    public static function sendFireBaseRequest(FireBaseMessage $raw) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array
        (
            'Authorization: key=' . self::$API_ACCESS_KEY,
            'Content-Type: application/json'
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($raw->getPostFields()));
        $result = curl_exec($ch);
        \ChromePhp::info($result);
        curl_close($ch);
    }
    
}

class FireBaseMessage {
    protected $postFields = array();
    const NORMAL = "normal";
    const HIGH = "high";
    
    /**
     * FireBaseRaw constructor.
     *
     * @param $receiver string
     */
    function __construct($receiver) {
        $this->postFields["to"] = $receiver;
    }
    
    /**
     * Send message
     */
    function send() {
        FireBase::sendFireBaseRequest($this);
    }
    
    /**
     * @param $token string
     *
     * @return FireBaseMessage
     */
    public function addReceiver($token) {
        if (!isset($this->postFields['registration_ids'])) {
            $this->postFields['registration_ids'] = array();
        }
        array_push($this->postFields['registration_ids'], $token);
        
        return $this;
    }
    
    /**
     * @param $tokens array
     *
     * @return FireBaseMessage
     */
    public function addReceivers(...$tokens) {
        foreach ($tokens as $token) {
            $this->addReceiver($token);
        }
        
        return $this;
    }
    
    /**
     * @param $priority string
     *
     * @return FireBaseMessage
     */
    public function setPriority($priority) {
        $this->postFields['priority'] = $priority;
        
        return $this;
    }
    
    /**
     * @param $key   string
     * @param $value string
     *
     * @return FireBaseMessage
     */
    public function addData($key, $value) {
        if (!isset($this->postFields['data'])) {
            $this->postFields['data'] = array();
        }
        $this->postFields['data'][$key] = $value;
        
        return $this;
    }
    
    public function getPostFields() {
        return $this->postFields;
    }
}

class FireBaseNotification extends FireBaseMessage {
    
    /**
     * @param $title string
     *
     * @return FireBaseNotification
     */
    public function setTitle($title) {
        $this->postFields['notification']['title'] = $title;
        
        return $this;
    }
    
    /**
     * @param $body string
     *
     * @return FireBaseNotification
     */
    public function setBody($body) {
        $this->postFields['notification']['body'] = $body;
        
        return $this;
    }
    
    /**
     * @param $channelId string
     *
     * @return FireBaseNotification
     */
    public function setAndroidChannelId($channelId) {
        $this->postFields['notification']['android_channel_id'] = $channelId;
        
        return $this;
    }
    
    /**
     * @param $icon string
     *
     * @return FireBaseNotification
     */
    public function setIcon($icon) {
        $this->postFields['notification']['icon'] = $icon;
        
        return $this;
    }
    
    /**
     * @param $sound string
     *
     * @return FireBaseNotification
     */
    public function setSound($sound) {
        $this->postFields['notification']['sound'] = $sound;
        
        return $this;
    }
    
    /**
     * @param $tag string
     *
     * @return FireBaseNotification
     */
    public function setTag($tag) {
        $this->postFields['notification']['tag'] = $tag;
        
        return $this;
    }
    
    /**
     * @param $color string
     *
     * @return FireBaseNotification
     */
    public function setColor($color) {
        $this->postFields['notification']['color'] = $color;
        
        return $this;
    }
    
    /**
     * @param $action string
     *
     * @return FireBaseNotification
     */
    public function setClickAction($action) {
        $this->postFields['notification']['click_action'] = $action;
        
        return $this;
    }
    
    /**
     * @param $key string
     *
     * @return FireBaseNotification
     */
    public function setBodyLocKey($key) {
        $this->postFields['notification']['body_loc_key'] = $key;
        
        return $this;
    }
    
    /**
     * @param $args string
     *
     * @return FireBaseNotification
     */
    public function setBodyLocArgs($args) {
        $this->postFields['notification']['body_loc_args'] = $args;
        
        return $this;
    }
    
    /**
     * @param $key string
     *
     * @return FireBaseNotification
     */
    public function setTitleLocKey($key) {
        $this->postFields['notification']['title_loc_key'] = $key;
        
        return $this;
    }
    
    /**
     * @param $args string
     *
     * @return FireBaseNotification
     */
    public function setTitleLocArgs($args) {
        $this->postFields['notification']['title_loc_args'] = $args;
        
        return $this;
    }
    
}