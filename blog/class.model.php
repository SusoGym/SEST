<?php namespace blog;

use base\SuperModel;

class Model extends SuperModel {
    
    public function __construct() {
        parent::__construct("../cfg.ini");
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
        
        $this->connection->escape_stringDirect($start);
        $this->connection->escape_stringDirect($end);
        
        if ($start != null) {
            $start = "STR_TO_DATE('$start', '%Y-%m-%d %T')";
        }
        if ($end != null) {
            $end = "STR_TO_DATE('$end', '%Y-%m-%d %T')";
        }
        
        \ChromePhp::info("Fetching all posts from " . ($start == null ? "null" : $start) . " to " . ($end == null ? "null" : $end));
        
        if ($start != null && $end != null) {
            $range = "releasedate >= $start AND releasedate <= $end";
        } else if ($start == null && $end != null) {
            $range = "releasedate <= $end";
        } else if ($start != null && $end == null) {
            $range = "releasedate >= $start";
        }
        
        $result = $this->connection->selectAssociativeValues("SELECT * FROM blog_news WHERE $range ORDER BY releasedate DESC");
        
        if ($result == null) {
            \ChromePhp::info("Returning 0 blog posts.");
            
            return null;
        }
        
        $posts = array();
        
        foreach ($result as $post) {
            $body = $post['body'];
            $subject = $post['subject'];
            
            $body = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "", $body);
            
            array_push($posts, new Post(intval($post['id']), $body, $subject, intval($post['author']), $post['releasedate']));
        }
        
        \ChromePhp::info("Returning " . sizeof($posts) . " blog posts.");
        
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
        
        \ChromePhp::info("Fetching posts of id $id");
        
        $result = $this->connection->selectAssociativeValues("SELECT * FROM blog_news WHERE id='$id'");
        
        if ($result == null) {
            \ChromePhp::info("No post of id $id found!");
            
            return null;
        }
        $result = $result[0];
        
        $post = new Post(intval($result['id']), $result['body'], $result['subject'], intval($result['author']), $result['releasedate']);
        
        \ChromePhp::info("Found post of id $id with subject " . $post->getSubject());
        
        return $post;
    }
    
    /**
     * Pushes new post to database and returns it's insertion id
     *
     * @param $post \blog\Post
     *
     * @return Post
     */
    public function addPost($post) {
        
        \ChromePhp::info("Adding new post with subject " . $post->getSubject());
        
        $releaseDate = $post->getReleaseDate();
        
        if ($releaseDate == null) {
            $releaseDate = date("Y-m-d H:i:s", time());
        }
        
        $userId = $post->getAuthor();
        $subject = self::getConnection()->escape_string($post->getSubject());
        $body = self::getConnection()->escape_string($post->getBody());
        
        $id = $this->connection->insertValues("INSERT INTO blog_news(subject, body, releasedate, author) VALUES ('$subject', '$body', TIMESTAMP('$releaseDate'), '$userId')");
        
        $post->setId($id);
        $post->setReleaseDate($releaseDate);
        
        \ChromePhp::info("Added new post with subject " . $post->getSubject() . " and id " . $post->getId());
        
        return $post;
    }
    
    /**
     * Pushes post to database
     *
     * @param $post \blog\Post
     */
    public function updatePost($post) {
        \ChromePhp::info("Editing post with id " . $post->getId());
        
        $id = $post->getId();
        $subject = self::getConnection()->escape_string($post->getSubject());
        $body = self::getConnection()->escape_string($post->getBody());
        $author = $post->getAuthor();
        $releaseDate = self::getConnection()->escape_string($post->getReleaseDate());
        $this->connection->straightQuery("UPDATE blog_news SET subject='$subject', body='$body', author=$author, releasedate='$releaseDate' WHERE id='$id'");
        
        \ChromePhp::info("Successfully edited post with id " . $post->getId());
    }
    
    /**
     * Deletes post from database
     *
     * @param $post \blog\Post
     */
    public function deletePost($post) {
        \ChromePhp::info("Deleting post with id " . $post->getId());
        
        $id = $post->getId();
        $this->connection->straightQuery("DELETE FROM blog_news WHERE id='$id'");
        
        \ChromePhp::info("Successfully deleted post with id " . $post->getId());
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
        
        if ($result == null) {
            \ChromePhp::info("Searched for user with username '$username' found none");
            
            return null;
        }
        
        $user = new User($result[0]["id"], $result[0]['username'], $result[0]['permission'], $result[0]['displayname']);
        
        \ChromePhp::info("Searched for user with username '$username' found $user");
        
        return $user;
        
    }
    
    /**
     * Returns user by userId
     *
     * @param $id int userId
     *
     * @return User|null
     */
    public function getUserById($id) {
        
        $result = $this->connection->selectAssociativeValues("SELECT * FROM blog_user WHERE id='$id'");
        
        if ($result == null) {
            \ChromePhp::info("Searched for user with id $id found none");
            
            return null;
        }
        
        $user = new User($id, $result[0]['username'], intval($result[0]['permission']), $result[0]['displayname']);
        
        \ChromePhp::info("Searched for user with id $id found $user");
        
        return $user;
    }
    
    /**
     * Returns user authenticated by token-id or null if invalid login token
     *
     * @param $token string
     *
     * @return User|null if invalid token
     */
    public function getUserByToken($token) {
        
        $this->connection->escape_stringDirect($token);
        
        $this->cleanTokens();
        
        $result = $this->connection->selectAssociativeValues("SELECT * FROM blog_token WHERE token='$token';");
        
        if ($result == null) {
            \ChromePhp::info("Searched for user with the access token '$token' found none");
            
            return null;
        }
        
        $id = intval($result[0]['userId']);
        
        $user = $this->getUserById($id);
        
        \ChromePhp::info("Searched for user with the access token '$token' found $user");
        
        return $user;
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
        
        $result = $this->connection->selectAssociativeValues("SELECT * FROM blog_user WHERE (username='$username' OR id='$id' OR displayname='$displayName');");
        
        $query = "";
        
        if ($result == null) { // no user with given attributes ==> create
            $query = "INSERT INTO blog_user(id, username, permission, displayname) VALUES ('$id', '$username', $permission, '$displayName');";
            \ChromePhp::info("Creating new user in database " . $user);
        } else if (sizeof($result) == 1) { // one user with given attributes ==> update
            $query = "UPDATE blog_user SET username='$username', permission=$permission, displayname='$displayName' WHERE id='$id';";
            \ChromePhp::info("Updating existing user in database to " . $user);
        } else {
            \ChromePhp::info("Not pushing user to database as same user already exists $user");
            
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
        
        $this->connection->straightQuery("INSERT IGNORE INTO blog_token(token, userId, expire) VALUES('$token', '$userId', TIMESTAMP('$timestamp'));");
        
        \ChromePhp::info("Created new access token for user $userId: '$token'");
        
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
        $result = $this->connection->selectAssociativeValues("SELECT * FROM blog_token WHERE userId='$userId' AND expire IS NOT NULL;");
        if ($result == null)
            return $this->addAuthToken($userId, null, false);
        
        $token = $result[0]['token'];
        
        \ChromePhp::info("Getting token for user of id $userId: '$token'");
        
        return $token;
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
        
        $this->connection->escape_stringDirect($token);
        $result = $this->connection->selectAssociativeValues("SELECT * FROM blog_token WHERE token='$token' ORDER BY STR_TO_DATE(expire, '%Y-%m-%d %T');");
        if ($result == null) {
            \ChromePhp::info("Tried to get expiration date of token '$token' but it does not exist.");
            
            return null;
        }
        $expire = $result[0]['expire'];
        
        \ChromePhp::info("Fetched expiration date of token '$token': $expire");
        
        return $expire;
    }
    
    /**
     * Removes all expired tokens from the database
     */
    public function cleanTokens() {
        \ChromePhp::info("Cleaning database from expired tokens");
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
        
        $id = $this->getUserById($id);
        
        \ChromePhp::info("Created user '$username' with id ");
        
        return $id;
    }
    
    /**
     * @return array
     */
    public function getDrafts() {
        $db = $this->connection->selectAssociativeValues("SELECT * FROM blog_drafts");
        
        $drafts = array();
        
        if (!isset($db[0]))
            return $drafts;
        
        foreach ($db as $set) {
            array_push($drafts, new Draft($set['id'], $set['subject'], $set['body'], $set['author']));
        }
        
        return $drafts;
        
    }
    
    /**
     * @param $id int
     *
     * @return \blog\Draft
     */
    public function getDraft($id) {
        $db = $this->connection->selectAssociativeValues("SELECT * FROM blog_drafts WHERE id=$id");
        
        if (isset($db[0])) {
            $set = $db[0];
            
            return new Draft($set['id'], $set['subject'], $set['body'], $set['author']);
        }
        
        return null;
    }
    
    /**
     * @param $draft \blog\Draft
     *
     * @return \blog\Draft
     */
    public function addDraft($draft) {
        $body = $draft->getBody();
        $subject = $draft->getSubject();
        $authorId = $draft->getAuthor();
        
        $this->connection->escape_stringDirect($body, $subject);
        
        $comma = false;
        
        $fields = "";
        $values = "";
        
        if ($body != null) {
            $fields .= "body";
            $values .= "'$body'";
            $comma = true;
        }
        
        if ($subject != null) {
            if ($comma) {
                $fields .= ",";
                $values .= ",";
            }
            
            $fields .= "subject";
            $values .= "'$subject'";
            $comma = true;
        }
        
        if ($authorId != null) {
            if ($comma) {
                $fields .= ",";
                $values .= ",";
            }
            
            $fields .= "author";
            $values .= "'$authorId'";
            $comma = true;
        }
        
        $id = $this->connection->insertValues("INSERT INTO blog_drafts($fields) VALUES ($values)");
        
        $draft->setId($id);
        
        return $draft;
    }
    
    /**
     * @param $draft \blog\Draft
     */
    public function updateDraft($draft) {
        
        $body = $draft->getBody();
        $id = $draft->getId();
        $subject = $draft->getSubject();
        $authorId = $draft->getAuthor();
        
        $this->connection->escape_stringDirect($body, $subject);
        
        $old = $this->getDraft($id);
        
        $query = "";
        $bef = false;
        
        if ($old->getBody() != $body) {
            $query .= "body='$body'";
            $bef = true;
        }
        
        if ($old->getSubject() != $subject) {
            if ($bef) {
                $query .= ", ";
            }
            $query .= "subject='$subject'";
            $bef = true;
        }
        
        if ($old->getAuthor() != $authorId) {
            if ($bef) {
                $query .= ", ";
            }
            
            $query .= "author='$authorId'";
        }
        
        if ($query != "") {
            $this->connection->straightQuery("UPDATE blog_drafts SET $query WHERE id='$id'");
        }
        
    }
    
    /**
     * @param $draft \blog\Draft
     */
    public function publishDraft($draft) {
        $body = $draft->getBody();
        $id = $draft->getId();
        $subject = $draft->getSubject();
        $authorId = $draft->getAuthor();
        
        $this->connection->escape_stringDirect($body, $subject);
        
        throw new \Exception("Not implemented yet");
        //TODO
    }
    
    
    /**
     * @param $draft \blog\Draft
     */
    public function deleteDraft($draft) {
        $id = $draft->getId();
        $this->connection->straightQuery("DELETE FROM blog_drafts WHERE id=$id");
    }
    
    /**
     * @param $str string
     *
     * @return array
     */
    public function searchUsers($str) {
        $this->connection->escape_stringDirect($str);
        
        return $this->connection->selectAssociativeValues("SELECT * FROM `blog_user` WHERE username LIKE '%$str%' OR displayname LIKE '%$str%'");
    }
}
