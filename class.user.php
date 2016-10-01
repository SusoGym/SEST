<?php

require("class.model.php");

/**
 *User class used to get user related data easily
 */
class User
{

  private $type;

  /**
   *Construct method of User class
   *@param int id
   */
  function __construct($id)
  {
    $this->id = $id;
  }

  /**
   *Returns user ID
   *@return int id
   */
  function get_id()
  {
    return $this->id;
  }

  /**
   *Returns user name
   *@return string name
   */
  function get_name()
  {
    return $this->name;
  }

  /**
   *Returns user type (0 for admin, 1 for parent, 2 for teacher)
   *@return int type
   */
  function get_type()
  {
    return $this->type;
  }
}


/**
 *Parent class as subclass of User class
 */
class Parent extends User
{

  /**
   *Contructor of Parent class
   */
  function __construct()
  {
    $model = new Model();
    $this->children = $model->parent_get_children($this->id);
    $this->name = $model->parent_get_name($id);
    $this->$type = 1;
  }

  /**
   *Returns child(ren)'s id(s)
   *@return Array[] children
   */
  function get_children()
  {
    return $this->children;
  }

  /**
   *Returns all teachers that teach any of the parents children
   *@return Array[] teachers
   */
  function get_teachers()
  {
    $model = new Model();
    foreach ($this->children as $key => $value) {
      $classes[] = $model->student_get_class($value);
    }
    $teachers = array();
    foreach ($classes as $key => $value) {
      $teachers = array_merge($teachers, $model->class_get_teachers($value));
    }
    sort($teachers);
    $size = count($teachers);
    for ($i = 1; $i <= $size; $i++) {
      if ($teachers[$i] != $teachers[$i-1]) {
        $tchrs_f[] = $teachers[$i]
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
   */
  function __construct()
  {
    $this->name = $model->teacher_get_name($id);
    $this->type = 2;
  }
}

?>
