<?php namespace blog;

define('PERMISSION_EVERYTHING', 1);
define('PERMISSION_ADD_POST', 2);
define('PERMISSION_VIEW_EDITOR_PANEL', 4);
define('PERMISSION_DELETE_POST', 8);
define('PERMISSION_EDIT_POST', 16);
define('PERMISSION_CHANGE_PERMISSION', 32);
define('PERMISSION_CHANGE_DISPLAYNAME', 64);
define('PERMISSION_CHANGE_DISPLAYNAME_OTHER', 128);

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
        $this->displayName = $displayName;
        
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
}