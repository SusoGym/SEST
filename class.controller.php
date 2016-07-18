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
      if ($_SESSION['user']['type'] == 1) {
        $this->user = new Parent($_SESSION['user']);
      } elseif ($_SESSION['user']['type']) {
        $this->user = new Teacher($_SESSION['user']);
      }
    }
    if (isset($input['type'])) {
      switch ($input['type']) {
        case "login":
  	      # validate, then set session var
  	      break;
  	      
  	    case "booking":
  	      if ($input['booking']['action'] == "add") {
            $model->booking_add($input['booking']['slot'], $this->user->get_id(), $input['booking']['teacher']);
          } elseif ($input['booking']['action'] == "delete") {
            $model->booking_delete($input['booking']['slot'], $this->user->get_id());
          }
          break;
          
        case "register":
  	      # check, then write into database, then login (session var...)
  	      break;
  	      
  	    default:
  	      $this->tpl = "login";
          $this->display();
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
