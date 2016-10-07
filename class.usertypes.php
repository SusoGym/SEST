<?php

/**
 * Class Guardian represents parent user
 */
class Guardian extends User
{

    function __construct($userId)
    {
        $this->userId = $userId;
    }

}

/**
 * Class Teacher represents teacher user
 */
class Teacher extends User
{
    function __construct($userId)
    {
        $this->userId = $userId;
    }
}


/**
 * Class User abstract user object
 */
class User
{
    var $userId;

}