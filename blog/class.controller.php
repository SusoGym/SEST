<?php namespace blog;

class Controller extends Utility {
    
    /** @var bool defines whether this execution is back- or frontend */
    private $console = false;
    /** @var array User input */
    private $data;
    /** @var  Model instance of model */
    private $model;
    // Response Variables
    /** @var int The response code (based upon HTML codes) */
    private $code = 200;
    /** @var string The response status message */
    private $message = "OK";
    /** @var mixed */
    private $responseData = null;
    /** @var string */
    private $action = null;
    
    
    /**
     * Controller constructor.
     *
     * @param $data array input data
     */
    public function __construct($data) {
        $this->data = $data;
        $this->model = Model::getInstance();
        $this->console = self::getExistentAndValue($data, "console");
        
        if ($this->console) {
            header('Content-Type: text/json');
        }
    }
    
    function __destruct() {
        
        if (!self::$errorless_exit)
            return;
        
        $outputArray = array();
        
        if ($this->action != null) {
            $outputArray = array("action" => $this->action);
        }
        
        $outputArray = array_merge($outputArray, array("code" => $this->code, "message" => $this->message));
        
        if ($this->responseData != null) {
            $outputArray = array_merge($outputArray, array("payload" => $this->responseData));
        } else {
            $outputArray = array_merge($outputArray, array("payload" => null));
        }
        
        die(json_encode($outputArray, JSON_PRETTY_PRINT));
    }
    
    /**
     * Starts the process of data processing
     */
    public function go() {
        
        if (!$this->console) {
            $template = self::getOrFallBack($this->data, 'template', Utility::$DEFAULT_TEMPLATE);
            self::displayTemplate($template);
            
            return;
        }
        
        $responseData = null;
        $outputArray = array();
        
        // following will call methods with the same name as is requested in $data['action'] (method must not be private),
        // data will be provided if parameter count > 0
        if (isset($this->data['action']) && method_exists($this, $this->data['action'])) {
            
            $reflect = new \ReflectionMethod($this, $this->data['action']);
            
            if ($reflect->isProtected()) {
                $reflect->setAccessible(true);
                
                
                if ($reflect->getNumberOfParameters() == 0) {
                    $this->responseData = $reflect->invoke($this);
                } else {
                    $this->responseData = $reflect->invoke($this, $this->data);
                }
                $this->action = $reflect->getName();
                
                $outputArray = array("action" => $reflect->getName());
                die();
            }
        }
        
        $this->code = 404;
        $this->message = "Invalid or unknown action!";
        
    }
    
    /**
     * Creates response for missing arguments
     */
    private function missingArgs(...$names) {
        $this->code = 400;
        
        if (is_array($names[0]))
            $names = $names[0];
        
        $msg = "";
        
        for ($i = 0; $i < sizeof($names); $i++) {
            
            $msg .= "'" . $names[$i] . "'";
            if ($i != sizeof($names) - 1)
                $msg .= ", ";
            
        }
        
        $this->message = "Missing parameter" . (sizeof($names) > 1 ? "s" : "") . " $msg!";
        die();
    }
    
    /**
     * @param array ...$requested
     * @return array
     */
    private function handleParameters(...$requested) {
        $data = $this->data;
        $response = array();
        $missing = array();
        
        foreach ($requested as $request) {
            $value = Utility::getIgnoreCaseOrNull($data, $request);
            if ($value == null) {
                array_push($missing, $request);
            } else {
                $response[$request] = $value;
            }
        }
        
        if (sizeof($missing) != 0) {
            $this->missingArgs($missing);
        }
        
        return $response;
    }
    
    /**
     * @param array ...$requested
     * @return array
     */
    private function handleOptionalParameters(...$requested) {
        $data = $this->data;
        $response = array();
        
        foreach ($requested as $request) {
            $value = Utility::getIgnoreCaseOrNull($data, $request);
            $response[$request] = $value;
            
        }
        
        return $response;
    }
    
    // Processing methods [may have 1 arg to receive $data | may return $payload (preferably objects) | must be protected]
    // objects can be created with ->  (object) [ key1 => value1, key2 => value2, ... ]
    
    /**
     * / Action function \
     * Returns the news array in an specified date range
     *
     * @param startDate string {optional} The range beginning [yyyy-mm-dd hh:mm:ss]
     * @param endDate string {optional}   The range ending
     * @return array
     */
    protected function fetchNews() {
        $params = $this->handleOptionalParameters("startDate", "endDate");
        $news = $this->model->getNews($params['startDate'], $params['endDate']);
        
        return $news;
    }
    
    /**
     * / Action function \
     * Returns the user information about the requested user
     * At least one parameter must be given
     *
     * @param auth -token string returns information about token owner
     * @param userId int        returns information about user with specified userId
     * @param username string   returns information about user with specified username
     * @return array
     */
    protected function getUserInfo() {
        $params = $this->handleOptionalParameters("auth-token", "userId", "username");
        $user = null;
        if ($params['auth-token'] != null) {
            $user = $this->model->getUserByToken($params['auth-token']);
        } else if ($params['userId'] != null) {
            $user = $this->model->getUserById($params['userId']);
        } else if ($params['username']) {
            $user = $this->model->getUserByName($params['username']);
        } else {
            $this->missingArgs("auth-token' or 'userId' or 'username");
        }
        
        if ($user == null) {
            
            $this->code = 404;
            $this->message = "Invalid user identifier given!";
        } else {
            return $user->getData();
        }
        
    }
    
    /**
     * / Action function \
     * Pushes new news post to database
     *
     * @param auth -token string
     * @param subject string
     * @param body string
     * @param releaseDate string {optional} [yyyy-mm-dd hh:mm:ss] // default: current timestamp
     * @return array
     */
    protected function addNews() {
        $params = array_merge($this->handleParameters("auth-token", "subject", "body"), $this->handleOptionalParameters("releaseDate"));
        
        $user = $this->model->getUserByToken($params['auth-token']);
        
        if ($user == null) {
            $this->code = 401;
            $this->message = "Invalid auth-token!";
            
            return null;
        }
        if (!$user->hasPermission(PERMISSION_ADD_NEWS)) {
            $this->code = 401;
            $this->message = "User does not have the permission to post news!";
            
            return null;
        }
        $postId = $this->model->addNews($user, $params['subject'], $params['body'], $params['releaseDate']);
        
        return array("postId" => $postId);
    }
    
    /**
     * / Action function \
     * Generates Auth-Token from stored SESSION data {user:[mail, pwd]}
     *
     * @return array
     */
    protected function createTokenFromSession() {
        if (!isset($_SESSION['user']['mail']) || !$_SESSION['user']['pwd']) {
            $this->code = 400;
            $this->message = "No login-data saved in session!";
            
            return null;
        }
        $username = $_SESSION['user']['mail'];
        $pwd = $_SESSION['user']['pwd'];
        
        $token = Utility::generateAuthToken($username, $pwd);
        
        if ($token == null) {
            $this->code = 401;
            $this->message = "Invalid login-data!";
            
            return null;
        }
        
        return array("authToken" => $token, "expire" => $this->model->getExpirationDate($token), "user" => $this->model->getUserByToken($token)->getData());
    }
    
    /**
     * / Action function \
     * Generates Auth-Token from given login-data
     *
     * @param username string
     * @param password string
     * @return array
     */
    protected function createToken() {
        $param = $this->handleParameters("username", "password");
        
        $token = Utility::generateAuthToken($param['username'], $param['password']);
        
        if ($token == null) {
            $this->code = 401;
            $this->message = "Invalid login-data!";
            
            return null;
        }
        
        return array("authToken" => $token, "expire" => $this->model->getExpirationDate($token), "user" => $this->model->getUserByToken($token)->getData());
    }
}