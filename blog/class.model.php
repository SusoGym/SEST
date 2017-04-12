<?php namespace blog;

class Model {
    
    /** @var $instance Model */
    private static $instance;
    
    /** Returns instance of model
     *
     * @return Model
     */
    public static function getInstance() {
        return self::$instance == null ? self::$instance = new Model() : self::$instance;
    }
    
    /** @var  \Connection */
    private $connection;
    
    private function __construct() {
        \Connection::$configFile = "../cfg.ini";
        $this->connection = new \Connection();
    }
    
    /**
     * Returns Posts in specified date-range
     *
     * @param $start string in format yyyy-mm-dd hh:mm:ss
     * @param $end   string  in format yyyy-mm-dd hh:mm:ss
     *
     * @return array
     */
    public function getPosts($start = null, $end = null) {
        $range = "1";
        
        if ($start != null) {
            $start = "STR_TO_DATE('$start', '%Y-%m-%d %T')";
        }
        if ($end != null) {
            $end = "STR_TO_DATE('$end', '%Y-%m-%d %T')";
        }
        
        if ($start != null && $end != null) {
            $range = "releasedate >= $start AND releasedate <= $end";
        } else if ($start == null && $end != null) {
            $range = "releasedate <= $end";
        } else if ($start != null && $end == null) {
            $range = "releasedate >= $start";
        }
        
        $result = $this->connection->selectAssociativeValues("SELECT * FROM blog_news WHERE $range ORDER BY releasedate");
        
        if ($result == null)
            return null;
        
        $posts = array();
        
        foreach ($result as $post) {
            array_push($posts, new Post(intval($post['id']), $post['body'], $post['subject'], intval($post['author']), $post['releasedate']));
        }
        
        return $posts;
        
    }
    
    /**
     * Returns post of specified id
     *
     * @param $id
     *
     * @return Post | null if not existent
     */
    public function getPost($id) {
        
        $result = $this->connection->selectAssociativeValues("SELECT * FROM blog_news WHERE id=$id");
        
        if ($result == null)
            return null;
        $result = $result[0];
        
        return new Post(intval($result['id']), $result['body'], $result['subject'], intval($result['author']), $result['releasedate']);
    }
    
    /**
     * Pushes new post to database and returns it's insertion id
     *
     * @param $post \blog\Post
     *
     * @return Post
     */
    public function addPost($post) {
        
        $releaseDate = $post->getReleaseDate();
        
        if ($releaseDate == null) {
            $releaseDate = date("Y-m-d H:i:s", time());
        }
        
        $userId = $post->getAuthor();
        $subject = $post->getSubject();
        $body = $post->getBody();
        
        $id = $this->connection->insertValues("INSERT INTO blog_news(subject, body, releasedate, author) VALUES ('$subject', '$body', TIMESTAMP('$releaseDate'), '$userId')");
        
        $post->setId($id);
        $post->setReleaseDate($releaseDate);
        
        return $post;
    }
    
    /**
     * Pushes post to database
     *
     * @param $post \blog\Post
     */
    public function updatePost($post) {
        $id = $post->getId();
        $subject = $post->getSubject();
        $body = $post->getBody();
        $author = $post->getAuthor();
        $releaseDate = $post->getReleaseDate();
        $this->connection->straightQuery("UPDATE blog_news SET subject='$subject', body='$body', author=$author, releasedate='$releaseDate' WHERE id=$id");
    }
    
    /**
     * Returns UserId by username, creates user if not existent
     *
     * @param $username
     *
     * @return User
     */
    public function getUserByName($username) {
        $this->connection->escape_stringDirect($username);
        $result = $this->connection->selectAssociativeValues("SELECT * FROM blog_user WHERE username LIKE '$username'");
        
        if ($result == null)
            return null;
        
        return new User($result[0]["id"], $result[0]['username'], $result[0]['permission'], $result[0]['displayname']);
        
    }
    
    /**
     * Returns user by userId
     *
     * @param $id int userId
     *
     * @return User|null
     */
    public function getUserById($id) {
        
        $result = $this->connection->selectAssociativeValues("SELECT * FROM blog_user WHERE id=$id");
        
        if ($result == null)
            return null;
        
        return new User($id, $result[0]['username'], intval($result[0]['permission']), $result[0]['displayname']);
    }
    
    /**
     * Returns user authenticated by token-id or null if invalid login token
     *
     * @param $token string
     *
     * @return User|null if invalid token
     */
    public function getUserByToken($token) {
        
        $this->cleanTokens();
        
        $result = $this->connection->selectAssociativeValues("SELECT * FROM blog_token WHERE token='$token';");
        
        if ($result == null)
            return null;
        
        $id = intval($result[0]['userId']);
        
        return $this->getUserById($id);
    }
    
    /**
     * Pushes user object to database
     *
     * @param $user User
     *
     * @return bool success
     */
    public function pushUser($user) {
        
        $username = $user->getUsername();
        $id = $user->getId();
        $displayName = $user->getDisplayName();
        $permission = $user->getPermission();
        
        $this->connection->escape_stringDirect($username);
        $this->connection->escape_stringDirect($displayName);
        
        $result = $this->connection->selectAssociativeValues("SELECT * FROM blog_user WHERE (username='$username' OR id=$id OR displayname='$displayName');");
        
        $query = "";
        
        if ($result == null) { // no user with given attributes ==> create
            $query = "INSERT INTO blog_user(id, username, permission, displayname) VALUES ($id, '$username', $permission, '$displayName');";
        } else if (sizeof($result) == 1) { // one user with given attributes ==> update
            $query = "UPDATE blog_user SET username='$username', permission=$permission, displayname='$displayName' WHERE id=$id;";
        } else {
            return false; // multiple users with same attributes
        }
        
        $this->connection->straightQuery($query);
        
        return true;
    }
    
    /**
     * Pushes auth-token to database
     *
     * @param $userId int
     * @param $token  null|string token to be pushed
     * @param $clean  bool whether or not to clean expired tokens
     *
     * @return string the pushed token
     */
    public function addAuthToken($userId, $token = null, $clean = true) {
        if ($clean) {
            $this->cleanTokens();
        }
        if ($token == null) {
            $token = uniqid() . uniqid();
        }
        $this->connection->escape_stringDirect($token);
        $timestamp = date("Y-m-d H:i:s", time() + 3600 * 12); // 12h
        
        $this->connection->straightQuery("INSERT IGNORE INTO blog_token(token, userId, expire) VALUES('$token', $userId, TIMESTAMP('$timestamp'));");
        
        return $token;
    }
    
    /**
     * Gets token from database that was auto-generated
     *
     * @param $userId int
     *
     * @return string token
     */
    public function getToken($userId) {
        $this->cleanTokens();
        $result = $this->connection->selectAssociativeValues("SELECT * FROM blog_token WHERE userId=$userId AND expire IS NOT NULL;");
        if ($result == null)
            return $this->addAuthToken($userId, null, false);
        
        return $result[0]['token'];
    }
    
    /**
     * Returns the expiration date of the given token
     *
     * @param $token string
     *
     * @return string|null if not a token
     */
    public function getExpirationDate($token) {
        $this->cleanTokens();
        $result = $this->connection->selectAssociativeValues("SELECT * FROM blog_token WHERE token='$token' ORDER BY STR_TO_DATE(expire, '%Y-%m-%d %T');");
        if ($result == null)
            return null;
        
        return $result[0]['expire'];
    }
    
    /**
     * Removes all expired tokens from the database
     */
    public function cleanTokens() {
        $this->connection->straightQuery("DELETE FROM blog_token WHERE expire < NOW();");
    }
    
    /**
     * Inserts user to database and returns user object
     *
     * @param $username
     *
     * @return User
     */
    public function createUserByName($username) {
        $this->connection->escape_stringDirect($username);
        $id = $this->connection->insertValues("INSERT INTO blog_user(username, permission) VALUES ('$username', 0)");
        
        return $this->getUserById($id);
    }
    
}