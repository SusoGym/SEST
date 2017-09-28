<?php namespace blog;


class Post implements \JsonSerializable {
    /** @var int */
    private $id;
    /** @var string */
    private $body;
    /** @var string */
    private $subject;
    /** @var int */
    private $author;
    /** @var string */
    private $releaseDate;
    
    /**
     * @param $body    string
     * @param $subject string
     * @param $author  \blog\User
     *
     * @return \blog\Post
     */
    public static function generatePost($body, $subject, $author, $releaseDate = null) {
        return new Post(null, $body, $subject, $author->getId(), $releaseDate);
    }
    
    /**
     * Post constructor.
     *
     * @param $id          int
     * @param $body        string
     * @param $subject     string
     * @param $author      int
     * @param $releaseDate string
     */
    public function __construct($id = null, $body, $subject, $author, $releaseDate = null) {
        $this->id = $id;
        $this->body = $body;
        $this->subject = $subject;
        $this->author = $author;
        $this->releaseDate = $releaseDate;
    }
    
    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }
    
    /**
     * @param int $id
     */
    public function setId($id) {
        $this->id = $id;
    }
    
    /**
     * @return string
     */
    public function getBody() {
        return $this->body;
    }
    
    /**
     * @param string $body
     */
    public function setBody($body) {
        $this->body = $body;
    }
    
    /**
     * @return string
     */
    public function getSubject() {
        return $this->subject;
    }
    
    /**
     * @param string $subject
     */
    public function setSubject($subject) {
        $this->subject = $subject;
    }
    
    /**
     * @return int
     */
    public function getAuthor() {
        return $this->author;
    }
    
    /**
     * @param int $author
     */
    public function setAuthor($author) {
        $this->author = $author;
    }
    
    /**
     * @return string
     */
    public function getReleaseDate() {
        return $this->releaseDate;
    }
    
    /**
     * @param string $releaseDate
     */
    public function setReleaseDate($releaseDate) {
        $this->releaseDate = $releaseDate;
    }
    
    /**
     * @return \blog\User
     */
    public function getAuthorObject() {
        return Model::getInstance()->getUserById($this->getAuthor());
    }
    
    /**
     * Push this post to the database / update this post in the database if already pushed
     *
     * @return \blog\Post
     */
    public function post() {
        
        if ($this->getId() == null) {
            Model::getInstance()->addPost($this);
        } else {
            Model::getInstance()->updatePost($this);
        }
        
        return $this;
    }
    
    /**
     * Delete Post
     *
     * @return \blog\Post
     */
    public function delete() {
        
        if ($this->getId() !== null) {
            Model::getInstance()->deletePost($this);
        }
        
        return $this;
    }
    
    function jsonSerialize() {
        return array("id" => $this->getId(), "subject" => $this->getSubject(), "body" => $this->getBody(), "releaseDate" => $this->getReleaseDate(), "authorId" => $this->getAuthor(), "authorObject" => $this->getAuthorObject());
    }
    
}
