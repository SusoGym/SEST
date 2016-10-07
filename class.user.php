<?php

/**
 *User class used to get user related data easily
 */
class User
{

  protected $type;
  protected $name;
  protected $id;
  protected $model;

  /**
   *Construct method of User class
   *@param int $id userId
   */
  function __construct($id)
  {
    $this->id = $id;
    $this->model = Model::getInstance();
  }

  /**
   *Returns user ID
   *@return int id
   */
  function getId()
  {
    return $this->id;
  }

  /**
   *Returns user name
   *@return string name
   */
  function getName()
  {
    return $this->name;
  }

  /**
   *Returns user type (0 for admin, 1 for parent, 2 for teacher)
   *@return int type
   */
  function getType()
  {
    return $this->type;
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
   * Contructor of Parent class
   * @param int $id userId
   */
  function __construct($id)
  {
    parent::__construct($id);

    $this->children = $this->model->parentGetChildren($this->id);
    $this->name = $this->model->parentGetName($this->id);
    $this->type = 1;
  }

  /**
   *Returns child(ren)'s id(s)
   *@return array[] children
   */
  function getChildren()
  {
    return $this->children;
  }

  /**
   *Returns all teachers that teach any of the parents children
   *@return array[] teachers
   */
  function getTeachers()
  {

      if($this->getChildren() == null)
          return array();

    $model = $this->model;
    $classes = array();
    foreach ($this->getChildren() as $key => $value) {
      $classes[] = $model->studentGetClass(intval($value));
    }
    $teachers = array();
    foreach ($classes as $key => $class) {
      $teachers = array_merge($teachers, $model->classGetTeachers($class));
    }

    sort($teachers);

    $tchrs_f = array();

    for ($i = 1; $i <= count($teachers); $i++) {
      if ($teachers[$i] != $teachers[$i-1]) {
        $tchrs_f[] = $teachers[$i];
      }
    }
    return $tchrs_f;
  }
}


/**
 *Teacher class as subclass of User class
 */
class Teacher extends User
{

  /**
   *Constructor of Teacher class
   * @param int $id userId
   */
  function __construct($id)
  {
    parent::__construct($id);

    $this->name = $this->model->teacherGetName($id);
    $this->type = 2;
  }
}

?>
