<?php
namespace api;

use \base\Printable;

class FCMConnection extends Printable
{
    /**
     * @var $id int unique identifier of this connection
     * @var $userId int the userId this connection is leading to
     * @var $userType int the userType this connection is leading to (3-Student, 2-Teacher, 1-Parent)
     * @var $token string the token this connection belongs to
     * @var $user User userId and userType combined
     */
    private $id, $userId, $userType, $token, $user;

    /**
     * FCMConnection constructor.
     * @param $id int
     * @param $userId int
     * @param $userType int
     * @param $token string
     */
    function __construct($id, $userId, $userType, $token)
    {
        $this->id = $id;
        $this->userId = $userId;
        $this->userType = $userType;
        $this->token = $token;
        $this->user = Model::getInstance()->getUserByIdAndType($userId, $userType);
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @return mixed
     */
    public function getUserType()
    {
        return $this->userType;
    }

    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return array[String=>mixed]
     */
    public function getData()
    {
        return array("id" => $this->getId(), "token" => $this->getToken(), "userId" => $this->getUserId(), "userType" => $this->getUserType(), "user" => $this->getUser());
    }

    /**
     * @return string
     */
    public function getClassType()
    {
        return "FCMConnection";
    }
}

class User extends Printable
{

    /**
     * @var $userId int
     * @var $userType int (3-Student, 2-Teacher, 1-Parent)
     * @var $surname string
     * @var $name string
     * @var $loginName string
     */
    private $userId, $userType, $surname, $name, $loginName;

    function __construct($userId, $userType, $surname, $name, $loginName)
    {
        $this->userId = $userId;
        $this->userType = $userType;
        $this->surname = $surname;
        $this->name = $name;
        $this->loginName = $loginName;

    }

    public function getClassType()
    {
        $type = "User";

        switch ($this->userType)
        {
            case 3:
                $type = "Student";
                break;
            case 2:
                $type = "Teacher";
                break;
            case 1:
                $type = "Parent";
                break;
        }

        return $type;
    }

    public function getData()
    {
        return array("userId" => $this->userId, "userType" => $this->userType, "surname" => $this->surname, "name" => $this->name, "loginName" => $this->loginName);
    }
}