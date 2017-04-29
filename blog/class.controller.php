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
        $this->console = isset($data["console"]);//self::getExistentAndValue($data, "console");

        if ($this->console) {
            header('Content-Type: text/json');
        }
    }

    /**
     * Deconstruct. Combines all the result of the processing of the input in a json response
     */
    function __destruct() {

        if (!$this->console)
            return;

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
     * Creates response for being unauthorized
     */
    private function unauthorized() {
        $this->code = 401;
        $this->message = "User is not allowed to perform this action!";
        die();
    }

    /**
     * Return array with all requested parameters, if existent in $data use that value, else call missingArgs()
     *
     * @param array ...$requested
     *
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
     * Return array with all requested parameters, if existent in $data use that value, else use null
     *
     * @param array ...$requested
     *
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

    /**
     * Returns User object from token or exit with 401 when token is invalid
     *
     * @param $token string
     *
     * @return \blog\User|null
     */
    private function getTokenUser($token) {
        $user = $this->model->getUserByToken($token);

        if ($user == null) {
            $this->code = 401;
            $this->message = "Invalid auth_token!";

            die();
        }

        return $user;
    }

    // Processing methods [may have 1 arg to receive $data | may return $payload (preferably objects) | must be protected]
    // objects can be created with ->  (object) [ key1 => value1, key2 => value2, ... ]

    /**
     * / Action function \
     * Returns the posts as array in the specified date range
     *
     * @param startDate string {optional} The range beginning [yyyy-mm-dd hh:mm:ss]
     * @param endDate   string {optional}   The range ending
     *
     * @return array
     */
    protected function fetchPosts() {
        $params = $this->handleOptionalParameters("startDate", "endDate");
        $news = $this->model->getPosts($params['startDate'], $params['endDate']);

        return $news;
    }

    /**
     * / Action function \
     * Pushes new news post to database
     *
     * Permission: PERMISSION_ADD_POST
     *
     * @param auth_token  string
     * @param subject     string
     * @param body        string
     * @param releaseDate string {optional} [yyyy-mm-dd hh:mm:ss] // default: current timestamp
     *
     * @return \blog\Post
     */
    protected function addPost() {
        $params = array_merge($this->handleParameters("auth_token", "subject", "body"), $this->handleOptionalParameters("releaseDate"));

        $user = $this->getTokenUser($params['auth_token']);

        if (!$user->hasPermission(PERMISSION_ADD_POST)) {
            $this->unauthorized();
        }

        $post = Post::generatePost($params['body'], $params['subject'], $user, $params['releaseDate'])->post();

        return $post;
    }

    /**
     * / Action function \
     * Edit already pushed post
     *
     * Permission: PERMISSION_EDIT_POST
     *
     * @param postId     int
     * @param auth_token string
     * @param subject    string
     * @param body       string
     *
     * @return \blog\Post
     */
    protected function editPost() {
        $param = array_merge($this->handleParameters("postId", "auth_token"), $this->handleOptionalParameters("body", "subject", "author", "releaseDate"));

        if (!$this->getTokenUser($param['auth_token'])->hasPermission(PERMISSION_EDIT_POST))
            $this->unauthorized();

        $post = $this->model->getPost($param['postId']);
        if ($param['body'] != null) {
            $post->setBody($param['body']);
        }
        if ($param['subject'] != null) {
            $post->setSubject($param['subject']);
        }
        if ($param['author'] != null) {
            $post->setAuthor($param['author']);
        }
        if ($param['releaseDate'] != null) {
            $post->setReleaseDate($param['releaseDate']);
        }

        $post->post();

        return $post;
    }

    /**
     * / Action function \
     * Returns the user information about the requested user
     * At least one parameter must be given
     *
     * @param auth     -token string returns information about token owner
     * @param userId   int        returns information about user with specified userId
     * @param username string   returns information about user with specified username
     *
     * @return \blog\User
     */
    protected function getUserInfo() {
        $params = $this->handleOptionalParameters("auth_token", "userId", "username");
        $user = null;
        if ($params['auth_token'] != null) {
            $user = $this->model->getUserByToken($params['auth_token']);
        } else if ($params['userId'] != null) {
            $user = $this->model->getUserById($params['userId']);
        } else if ($params['username']) {
            $user = $this->model->getUserByName($params['username']);
        } else {
            $this->missingArgs("auth_token' or 'userId' or 'username");
        }

        if ($user == null) {

            $this->code = 404;
            $this->message = "Invalid user identifier given!";
        } else {
            return $user;
        }

    }

    /**
     * / Action function \
     * Generates auth_token from stored SESSION data {user:[mail, pwd]}
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

        return array("authToken" => $token, "expire" => $this->model->getExpirationDate($token), "user" => $this->model->getUserByToken($token));
    }

    /**
     * / Action function \
     * Generates auth_token from given login-data
     *
     * @param username string
     * @param password string
     *
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

        return array("authToken" => $token, "expire" => $this->model->getExpirationDate($token), "user" => $this->model->getUserByToken($token));
    }

    /**
     * / Action function \
     * Returns all permissions and their bitwise value
     *
     * @return array
     */
    protected function getPermissions() {
        $consts = get_defined_constants(true)['user'];
        $perms = array();

        $prefix = "PERMISSION_";

        foreach ($consts as $key => $value) {
            if (substr($key, 0, strlen($prefix)) === $prefix) {
                $perms[$key] = $value;
            }
        }

        return $perms;
    }

    /**
     * / Action function \
     * Returns whether or not the specified user has the specified permission | needs one user argument
     *
     * @param permission int
     * @param auth_token string
     * @param userId     int
     * @param username   string
     *
     * @return array
     */
    protected function hasPermission() {
        $params = array_merge($this->handleOptionalParameters("auth_token", "userId", "username"), $this->handleParameters("permission"));
        $user = null;
        if ($params['auth_token'] != null) {
            $user = $this->model->getUserByToken($params['auth_token']);
        } else if ($params['userId'] != null) {
            $user = $this->model->getUserById($params['userId']);
        } else if ($params['username']) {
            $user = $this->model->getUserByName($params['username']);
        } else {
            $this->missingArgs("auth_token' or 'userId' or 'username");
        }
        if ($user == null) {

            $this->code = 404;
            $this->message = "Invalid user identifier given!";
            die();
        }

        return array("permission" => intval($params['permission']), "success" => $user->hasPermission($params['permission']), "user" => $user);

    }

    /**
     * / Action function \
     * Changes the permissions of the specified user | needs one user argument
     *
     * @param permission      int
     * @param value           bool
     * @param auth_token      string
     * @param user_auth_token string
     * @param userId          int
     * @param username        string
     *
     * @return array
     */
    protected function changePermission() {
        $params = array_merge($this->handleOptionalParameters("user_auth_token", "userId", "username", "mode"), $this->handleParameters("auth_token", "permission", "value"));
        $user = null;
        if ($params['user_auth_token'] != null) {
            $user = $this->model->getUserByToken($params['user_auth_token']);
        } else if ($params['userId'] != null) {
            $user = $this->model->getUserById($params['userId']);
        } else if ($params['username']) {
            $user = $this->model->getUserByName($params['username']);
        } else {
            $this->missingArgs("user_auth_token' or 'userId' or 'username");
        }

        if (!$this->getTokenUser($params['auth_token'])->hasPermission(PERMISSION_CHANGE_PERMISSION)) {
            $this->unauthorized();
        }

        if ($user == null) {

            $this->code = 404;
            $this->message = "Invalid user identifier given!";
            die();
        }

        $user->setPermission(intval($params['permission']), boolval($params['value']));

        return array("permission" => intval($params['permission']), "success" => $user->pushChanges(), "user" => $user);
    }

    /**
     * / Action function \
     * Changes the display-name of the specified user | needs one user argument
     *
     * @param permission      int
     * @param value           bool
     * @param auth_token      string
     * @param user_auth_token string
     * @param userId          int
     * @param username        string
     *
     * @return array
     */
    protected function changeDisplayName() {
        $params = array_merge($this->handleOptionalParameters("user_auth_token", "userId", "username", "mode"), $this->handleParameters("auth_token", "displayName"));
        $user = null;
        if ($params['user_auth_token'] != null) {
            $user = $this->model->getUserByToken($params['user_auth_token']);
        } else if ($params['userId'] != null) {
            $user = $this->model->getUserById($params['userId']);
        } else if ($params['username']) {
            $user = $this->model->getUserByName($params['username']);
        } else {
            $this->missingArgs("auth_token' or 'userId' or 'username");
        }

        $executer = $this->getTokenUser($params['auth_token']);


        if (($executer != $user && !$executer->hasPermission(PERMISSION_CHANGE_DISPLAYNAME_OTHER)) || ($executer == $user && !$executer->hasPermission(PERMISSION_CHANGE_DISPLAYNAME)))
            $this->unauthorized();

        if ($user == null) {

            $this->code = 404;
            $this->message = "Invalid user identifier given!";
            die();
        }

        $user->setDisplayName($params['displayName']);

        return array("displayName" => $params['displayName'], "success" => $user->pushChanges(), "user" => $user);

    }
}
