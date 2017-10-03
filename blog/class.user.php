<?php namespace blog;

define('PERMISSION_EVERYTHING', 1); // all other permission
define('PERMISSION_ADD_POST', 2); // allowed to post
define('PERMISSION_VIEW_EDITOR_PANEL', 4); // do we need this?
define('PERMISSION_DELETE_POST', 8); // allowed to delete post
define('PERMISSION_EDIT_POST', 16); // allowed to edit post
define('PERMISSION_CHANGE_PERMISSION', 32); // allowed to change permission if executor has the permission to be changed
define('PERMISSION_CHANGE_ALL_PERMISSION', 64); // allowed to change permission even if does not have same permission
define('PERMISSION_CHANGE_DISPLAYNAME', 128); // allowed to change own display-name
define('PERMISSION_CHANGE_DISPLAYNAME_OTHER', 256); // allowed to change other display-name

class User implements \JsonSerializable {
    
    /** @var $id int */
    private $id;
    /** @var $username string */
    private $username;
    /** @var $permission int */
    private $permission;
    /** @var $displayName string */
    private $displayName;
    
    public function __construct($id, $username, $permission, $displayName) {
        
        $this->id = intval($id);
        $this->username = $username;
        $this->permission = intval($permission);
        if ($displayName != "") {
            $this->displayName = $displayName;
        }
    }
    
    
    /**
     * Returns userId
     *
     * @return int
     */
    public function getId() {
        return $this->id;
    }
    
    /**
     * Returns username
     *
     * @return string
     */
    public function getUsername() {
        return $this->username;
    }
    
    /**
     * Returns permissions
     *
     * @return int
     */
    public function getPermission() {
        return $this->permission;
    }
    
    /**
     * Checks if user has specified permission
     *
     * @param $permission
     *
     * @return bool
     */
    public function hasPermission($permission) {
        if ($permission != PERMISSION_EVERYTHING && $this->hasPermission(PERMISSION_EVERYTHING))
            return true;
        
        return (($this->getPermission()) & $permission) == $permission;
    }
    
    /**
     * Sets specified permission for user
     *
     * @param $permission int
     * @param $value      bool
     */
    public function setPermission($permission, $value) {
        if ($value) {
            $this->permission |= $permission;
        } else {
            $this->permission &= ~$permission;
        }
    }
    
    /**
     * Returns displayname
     *
     * @return string
     */
    public function getDisplayName() {
        if ($this->displayName == null)
            return $this->username;
        
        return $this->displayName;
    }
    
    /**
     * @param string $displayName
     */
    public function setDisplayName($displayName) {
        $this->displayName = $displayName;
    }
    
    /**
     * Pushes this user object changes to the database
     *
     * @return bool success
     */
    public function pushChanges() {
        return Model::getInstance()->pushUser($this);
    }
    
    /**
     * Returns the content of this class as an array
     *
     * @return array
     */
    function jsonSerialize() {
        return array("id" => $this->id, "username" => $this->username, "permission" => $this->permission, "displayName" => $this->displayName);
    }
    
    public function __toString() {
        return "[User]" . json_encode($this);
    }
}