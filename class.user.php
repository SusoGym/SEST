<?php

/**
 *User class used to get user related data easily
 */
class User
{

  protected $type;
  protected $name;
  protected $id;

  /**
   *Construct method of User class
   *@param int $id userId
   */
  public function __construct($id)
  {
    $this->id = $id;
  }

  /**
   * @param $id int user id
   * @return User fitting extension of user (Guardian | Teacher)
   */
  public static function fetchFromDB($id)
  {
      $model = Model::getInstance();
      $type = $model->userGetType($id); // 0 - Admin; 1 - parent; 2 - teacher

      $user = null;

      if($type == 1)
          $user = new Guardian($id);
      else if($type == 2)
          $user = new Teacher($id);
      else
          die("No Admin implemented yet!");

      $user->type = $type;
      $user->name = $model->idGetUsername($id);

      return $user;
  }

  /**
   *Returns user ID
   *@return int id
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   *Returns user name
   *@return string name
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   *Returns user type (0 for admin, 1 for parent, 2 for teacher)
   *@return int type
   */
  public function getType()
  {
    return $this->type;
  }


  /**
   * @return string this class as string
   */
  public function __toString()
  {
    return "User{id=" . $this->id . ", name=\"" . $this->name . "\",type=" . $this->type . "}";
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
  public function __construct($id)
  {
    parent::__construct($id);

    $this->children = Model::getInstance()->parentGetChildren($this->id);
    $this->name = Model::getInstance()->parentGetName($this->id);
    $this->type = 1;
  }

  /**
   *Returns child(ren)'s id(s)
   *@return array[] children
   */
  public function getChildren()
  {
    return $this->children;
  }

  /**
   *Returns all teachers that teach any of the parents children
   *@return array[] teachers
   */
  public function getTeachers()
  {

      if($this->getChildren() == null)
          return array();

    $model = Model::getInstance();
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
  public function __construct($id)
  {
    parent::__construct($id);

    $this->name = Model::getInstance()->teacherGetName($id);
    $this->type = 2;
  }
}

?>
