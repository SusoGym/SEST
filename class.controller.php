<?php

/**
 *
 */
class Controller
{

  function __construct($input)
  {
    $model = new Model();
    if (isset($_SESSION['user'])) {
      $this->user = new User($_SESSION['user']);
    }
    if (isset($input['type'])) {
      if ($input['type'] == "login") {
  	# validate, then set session var
      } elseif ($input['type'] == "booking") {
        if ($input['booking']['action'] == "add") {
          $model->booking_add($input['booking']['slot'], $this->user->get_id(), $input['booking']['teacher']);
        } elseif ($input['booking']['action'] == "delete") {
          $model->booking_delete($input['booking']['slot'], $this->user->get_id());
        }
      } elseif ($input['type'] == "register") {
  	# check, then write into database, then login (session var...)
      }

    } else {
      $this->tpl = "login";
      $this->display();
    }
  }

  function display()
  {
    $view = new View($this->tpl);
  }

}

?>
