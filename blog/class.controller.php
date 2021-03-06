<?php namespace blog;

use base\SuperController;
use base\SuperUtility;

/**
 * Class Controller
 *
 * @package blog
 * @property Model $model
 */
class Controller extends SuperController {
    
    public function __construct(array $data) {
        $model = new Model();
        parent::__construct($data, $model);
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
     * Returns the requested post
     *
     * @param postId int the requested post
     *
     * @return \blog\Post|null
     */
    protected function fetchPost() {
        $params = $this->handleParameters("postId");
        
        return $this->model->getPost(intval($params['postId']));
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
        
        $body = $params['body'];
        $subject = $params['subject'];
        
        $body = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "", $body);
        
        $post = Post::generatePost($body, $subject, $user, $params['releaseDate'])->post();
        
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
     * Delete already pushed post
     *
     * Permission: PERMISSION_DELETE_POST
     *
     * @param postId     int
     * @param auth_token string
     * @param subject    string
     * @param body       string
     *
     * @return \blog\Post
     */
    protected function deletePost() {
        $param = $this->handleParameters("postId", "auth_token");
        
        if (!$this->getTokenUser($param['auth_token'])->hasPermission(PERMISSION_DELETE_POST))
            $this->unauthorized();
        
        $post = $this->model->getPost($param['postId']);
        $post->delete();
        
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
     * @return User
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
            
            $this->code = 400;
            $this->message = "Invalid user identifier given!";
        } else {
            return $user;
        }
        
        return null;
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
        
        if (strpos($params['permission'], ',') !== false) {
            $perms = explode(",", $params['permission']);
            $bool = false;
            $resp = array();
            foreach ($perms as $p) {
                $suc = $user->hasPermission($p, true);
                array_push($resp, array("permission" => intval($p), "success" => $suc));
                if ($suc) {
                    $bool = true;
                }
            }
            
            return array("permission" => $perms, "success" => $bool, "user" => $user, "data" => $resp);
            
            
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
        $params = array_merge($this->handleOptionalParameters("user_auth_token", "userId", "username"), $this->handleParameters("auth_token", "permission", "value"));
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
        
        $executor = $this->getTokenUser($params['auth_token']);
        
        if (!$executor->hasPermission(PERMISSION_CHANGE_PERMISSION) && !$executor->hasPermission(PERMISSION_CHANGE_ALL_PERMISSION)) {
            $this->unauthorized();
        }
        
        if ($user == null) {
            
            $this->code = 404;
            $this->message = "Invalid user identifier given!";
            die();
        }
        
        if (!$executor->hasPermission(intval($params['permission'])) && !$executor->hasPermission(PERMISSION_CHANGE_ALL_PERMISSION)) {
            $this->code = 401;
            $this->message = "Executor has to have the desired permission!";
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
        $params = array_merge($this->handleOptionalParameters("user_auth_token", "userId", "username"), $this->handleParameters("auth_token", "displayName"));
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
        
        $executor = $this->getTokenUser($params['auth_token']);
        
        
        if (($executor != $user && !$executor->hasPermission(PERMISSION_CHANGE_DISPLAYNAME_OTHER)) || ($executor == $user && !$executor->hasPermission(PERMISSION_CHANGE_DISPLAYNAME)))
            $this->unauthorized();
        
        if ($user == null) {
            
            $this->code = 404;
            $this->message = "Invalid user identifier given!";
            die();
        }
        
        $user->setDisplayName($params['displayName']);
        
        return array("displayName" => $params['displayName'], "success" => $user->pushChanges(), "user" => $user);
        
    }
    
    /**
     * / Action function \
     */
    protected function addDraft() {
        $params = array_merge($this->handleOptionalParameters("subject", "body", "author"), $this->handleParameters("auth_token"));
        
        if (!$this->getTokenUser($params['auth_token'])->hasPermission(PERMISSION_HANDLE_DRAFT)) {
            $this->unauthorized();
        }
        
        if ($params['subject'] == null && $params['body'] == null && $params['author'] == null) {
            $this->code = 404;
            $this->message = "At least one parameter (subject, body or author) has to be given";
            die();
        }
        
        $d = new Draft(null, $params['subject'], $params['body'], $params['author']);
        
        $d->push();
        
        return $d;
    }
    
    /**
     * / Action function \
     */
    protected function editDraft() {
        $params = array_merge($this->handleOptionalParameters("subject", "body", "author"), $this->handleParameters("auth_token", "draft_id"));
        
        if (!$this->getTokenUser($params['auth_token'])->hasPermission(PERMISSION_HANDLE_DRAFT)) {
            $this->unauthorized();
        }
        
        
        if ($params['subject'] == null && $params['body'] == null && $params['author'] == null) {
            $this->code = 404;
            $this->message = "At least one parameter (subject, body or author) has to be given";
            die();
        }
        
        $draft = $this->model->getDraft($params['draft_id']);
        
        if ($params['subject'] != null) {
            $draft->setSubject($params['subject']);
        }
        
        if ($params['body'] != null) {
            $draft->setBody($params['body']);
        }
        
        if ($params['author'] != null) {
            $draft->setAuthor($params['author']);
        }
        
        $draft->push();
        
        return $draft;
        
    }
    
    /**
     * / Action function \
     */
    protected function fetchDrafts() {
        $params = array_merge($this->handleParameters("auth_token"));
        
        if (!$this->getTokenUser($params['auth_token'])->hasPermission(PERMISSION_VIEW_DRAFT)) {
            $this->unauthorized();
        }
        
        $news = $this->model->getDrafts();
        
        return $news;
    }
    
    /**
     * / Action function \
     */
    protected function fetchDraft() {
        $params = array_merge($this->handleParameters("auth_token", "draft_id"));
        
        if (!$this->getTokenUser($params['auth_token'])->hasPermission(PERMISSION_VIEW_DRAFT)) {
            $this->unauthorized();
        }
        
        return $this->model->getDraft($params['draft_id']);
    }
    
    /**
     * / Action function \
     */
    protected function deleteDraft() {
        $params = array_merge($this->handleParameters("auth_token", "draft_id"));
        
        $user = $this->getTokenUser($params['auth_token']);
        
        if (!$user->hasPermission(PERMISSION_HANDLE_DRAFT)) {
            $this->unauthorized();
        }
        $this->model->deleteDraft(new Draft($params['draft_id'], null, null, null));
    }
    
    /**
     * / Action function \
     */
    protected function publishDraft() {
        $params = array_merge($this->handleParameters("auth_token", "draft_id"));
        
        $user = $this->getTokenUser($params['auth_token']);
        
        if (!$user->hasPermission(PERMISSION_VIEW_DRAFT) || !$user->hasPermission(PERMISSION_PUBLISH_DRAFT) || !$user->hasPermission(PERMISSION_ADD_POST)) {
            $this->unauthorized();
        }
        
        $this->model->publishDraft($this->model->getDraft($params['draft_id']));
        
    }
    
    protected function searchUsers() {
        $params = array_merge($this->handleParameters("auth_token", "query"));
        
        $user = $this->getTokenUser($params['auth_token']);
        
        if (!$user->hasPermission(PERMISSION_CHANGE_DISPLAYNAME) && !$user->hasPermission(PERMISSION_CHANGE_DISPLAYNAME_OTHER) && !$user->hasPermission(PERMISSION_CHANGE_PERMISSION) && !$user->hasPermission(PERMISSION_CHANGE_ALL_PERMISSION)) {
            $this->unauthorized();
        }
        
        $query = strtolower($params['query']);
        
        $data = $this->model->searchUsers($query);
        $names = array();
        if ($data != null) {
            foreach ($data as $user) {
                $username = $user['username'];
                $displayname = $user['displayname'];
                
                if (strpos(strtolower($username), $query) !== false) {
                    array_push($names, $username);
                }
                
                if (strpos(strtolower($displayname), $query) !== false) {
                    
                    array_push($names, $displayname);
                }
                
                
            }
        }
        $this->outputArray = array("suggestions" => $names);
        
        
        return $data;
    }
    
    
}
