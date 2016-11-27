<?php

/**
 * User class used to get user related data easily
 */
class User extends Printable
{

    /**
     * @var int 0 -> Admin, 1 -> Parent/Guardian, 2 -> Teacher
     */
    protected $type;
    /**
     * @var string username
     */
    protected $username;
    /**
     * @var int userId
     */
    protected $id;
    /**
     * @var user's email
     */
    protected $email;
    /**
     * @var $surname string Surname name of the user
     */
    protected $surname;
    /**
     * @var $surname string Name name of the user
     */
    protected $name;

    /**
     *Construct method of User class
     * @param int $id userId
     */
    public function __construct($id, $username, $type, $email, $name = null, $surname = null)
    {
        $this->id = $id;
        $this->username = $username;
        $this->type = $type;
        $this->email = $email;
        $this->name = $name;
        $this->surname = $surname;
    }

    /**
     *Returns user ID
     * @return int id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     *Returns username
     * @return string name
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     *Returns user type (0 for admin, 1 for parent, 2 for teacher)
     * @return int type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getFullname()
    {
        return $this->name . ' ' . $this->surname;
    }

    /**
     * @return User
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return array[String => Data] used for creating __toString and jsonSerialize
     */
    public function getData()
    {
        return array("userid" => $this->id, "username" => $this->username, "type" => $this->type, "name" => $this->name, "surname" => $this->surname, "email" => $this->email);
    }

    /** Returns class type
     * @return string
     */
    public function getClassType()
    {
        return "Student";
    }
}


/**
 * Guardian class as subclass of User class representing parents
 */
class Guardian extends User
{

    /**
     * @var array
     */
    private $children;
    /**
     * @var int
     */
    private $parentId;

    /**
     * Contructor of Parent class
     * @param int $id userId
     * @param string $username
     * @param string $email
     */
    public function __construct($id, $username, $email, $parentId)
    {
        $nameArr = Model::getInstance()->parentGetName($id);
        parent::__construct($id, $username, 1, $email, $nameArr['name'], $nameArr['surname']);

        $this->parentId = $parentId;
        $this->children = Model::getInstance()->getChildrenByParentUserId($this->id);

    }

    /**
     *Returns child(ren)'s id(s)
     * @return array[Student] children
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Returns all classes that are related to the parent's children
     * @return array[String]
     */
    public function getClasses()
    {
        if ($this->getChildren() == null)
            return array();

        $model = Model::getInstance();
        $classes = array();
        foreach ($this->getChildren() as $student/** @var $student Student */) {
            $classes[] = $student->getClass();
        }

        return $classes;
    }

    /**
     * Returns all teachers that teach any of the parents children
     * @return array[Teacher] teachers
     */
    public function getTeachers()
    {

        if ($this->getChildren() == null)
            return array();

        $model = Model::getInstance();
        $classes = $this->getClasses();

        $teachers = array();
        foreach ($classes as $class) {
            $teacher = $model->getTeachersByClass($class);
            if ($teacher == null)
                continue;
            $teachers = array_merge($teachers, $teacher);
        }

        sort($teachers);

        $tchrs_f = array();

        for ($i = 1; $i < sizeof($teachers); $i++) {
            if ($teachers[$i] != $teachers[$i - 1]) { // check duplicate
                $tchrs_f[] = $teachers[$i];
            }
        }
        return $tchrs_f;
    }

    /**
     * Returns all teachers for the children ordered by class
     * @return array[String => array(Teacher)]
     */
    public function getTeachersByClass()
    {
        if ($this->getChildren() == null)
            return array();

        $model = Model::getInstance();
        $classes = $this->getClasses();

        $teachers = array();
        foreach ($classes as $class) {
            $teacher = $model->getTeachersByClass($class);
            if ($teacher == null)
                continue;

            $teachers[$class] = $teacher;

        }

        return $teachers;
    }

    /**
     * @return int parent ID
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * @return array[String => mixed] returns all data of this class as array
     */
    public function getData()
    {
        return array_merge(parent::getData(), array("parentId" => $this->parentId, "children" => $this->children));
    }

    /** Returns class type
     * @return string
     */
    public function getClassType()
    {
        return "Guardian";
    }
}


/**
 *Teacher class as subclass of User class
 */
class Teacher extends User
{

    /**
     * @var int Teacher ID
     */
    protected $teacherId;

    /**
     * Contructor of Teacher class
     * @param int $id userId
     * @param string $username
     * @param string $email
     */
    public function __construct($id, $username, $email, $teacherId, $rawData = null)
    {


        $nameArr = Model::getInstance()->getTeacherNameByTeacherId($teacherId, $rawData);

        $this->teacherId = $teacherId;

        parent::__construct($id, $username, 2, $email, $nameArr['name'], $nameArr['surname']);
    }

    /**
     * @return int
     */
    public function getTeacherId()
    {
        return $this->teacherId;
    }

    /**
     * @return array[String => mixed] returns all data of this class as array
     */
    public function getData()
    {
        return array_merge(parent::getData(), array("teacherId" => $this->teacherId));
    }

    /** Returns class type
     * @return string
     */
    public function getClassType()
    {
        return "Teacher";
    }
}

class Admin extends User
{

    function __construct($id, $username, $email)
    {
        parent::__construct($id, $username, 0, $email);
    }

    /** Returns class type
     * @return string
     */
    public function getClassType()
    {
        return "Admin";
    }

}

/**
 * Class Student
 */
class Student extends Printable
{

    /**
     * @var int student ID
     */
    protected $id;
    /**
     * @var string student's class
     */
    protected $class;
    /**
     * @var string student's surname
     */
    protected $surname;
    /**
     * @var string student's name
     */
    protected $name;
    /**
     * @var int parent ID
     */
    protected $eid;
    /**
     * @var string student's birthday
     */
    protected $bday;

    public function __construct($id, $class, $surname, $name, $bday, $eid = null)
    {
        $this->id = $id;
        $this->class = $class;
        $this->surname = $surname;
        $this->name = $name;
        $this->bday = $bday;
        $this->eid = $eid;
    }


    /**
     * Returns student id
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return string
     */
    public function getSurname()
    {
        return $this->surname;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getEid()
    {
        return $this->eid;
    }

    public function getFullName()
    {
        return $this->getName() . " " . $this->getSurname();
    }

    /**
     * @return string
     */
    public function getBday()
    {
        return $this->bday;
    }

    /**
     * @return array[String => Data] used for creating __toString and jsonSerialize
     */
    public function getData()
    {
        return array("id" => $this->id, "class" => $this->class, "surname" => $this->surname, "name" => $this->name, "eid" => $this->eid, "bday" => $this->bday);
    }

    /** Returns class type
     * @return string
     */
    public function getClassType()
    {
        return "Student";
    }

    /**
     * Get all teachers teaching this student
     * @return array(Teachers)
     */
    public function getTeachers()
    {
        $model = Model::getInstance();

        $teachers = $model->getTeachersByClass($this->getClass());
        if ($teachers == null)
            return array();

        sort($teachers);

        return $teachers;
    }
}


?>
