<?php

require("class.model.php");

/**
 *
 */
class User
{

  function __construct($id, $set)
  {
    $model = new Model();
    if !(isset($set)) {
      $this->type = $model->user_get_type($id);
      if ($this->type == 1) {
        return new Parent($id, 0);
      } elseif ($this->type == 2) {
        return new Teacher($id, 0);
      }
    } else {
      $this->id = $id;
      $this->name = $model->user_get_name($id);
    }
  }

  function get_id()
  {
    return $this->id;
  }

  function get_name()
  {
    return $this->name;
  }

  function get_type()
  {
    return $this->type;
  }
}


/**
 *
 */
class Parent extends User
{

  function __construct()
  {
    $model = new Model();
    $this->children = $model->parent_get_children($this->id);
  }

  function get_children()
  {
    return $this->children;
  }

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
 *
 */
class Teacher extends User
{

  function __construct()
  {
    # code...
  }
}

?>
