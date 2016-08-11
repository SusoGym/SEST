<?php

/**
 *Controller class handles input and other data
 */
class Controller
{

  function __construct($input)
  {
    $model = new Model();

    //Create User object
    if (isset($_SESSION['user'])) {
      if ($_SESSION['user']['type'] == 1) {
        $this->user = new Parent($_SESSION['user']['id']);
      } elseif ($_SESSION['user']['type']) {
        $this->user = new Teacher($_SESSION['user']['id']);
      }
    }

    //Handle input
    if (isset($input['type'])) {
      switch ($input['type']) {
        case "login":
          if ($model->password_validate($input['login']['user'], $input['login']['password']) == true) {
            $_SESSION['user']['id'] = $model->user_name_get_id();
            $_SESSION['user']['type'] = $model->user_get_type($_SESSION['user']['id']);
            $this->tpl = "main";
    	      $this->infoToView = null;
    	      $this->display();
          } else {
            $this->tpl = "login";
    	      $this->infoToView = array('notifications' => array('Benutzername oder Passwort falsch'));
    	      $this->display();
          }
  	      break;

  	    case "booking":
  	      if ($input['booking']['action'] == "add") {
            $model->booking_add($input['booking']['slot'], $this->user->get_id(), $input['booking']['teacher']);
          } elseif ($input['booking']['action'] == "delete") {
            $model->booking_delete($input['booking']['slot'], $this->user->get_id());
          }
          $this->tpl = "main";
          $this->infoToView = null;
          $this->display();
          break;

        case "register":
  	      # check, then write into database, then login (session var...)
  	      break;

  	    case "logout":
  	      session_destroy();

  	      $this->tpl = "login";
  	      $this->infoToView = array('notifications' => array('Erfolgreich abgemeldet'));
  	      $this->display();
  	      break;

  	    default:
  	      session_destroy();

  	      $this->tpl = "login";
  	      $this->infoToView = array('notifications' => array('A fehler occurred'));
          $this->display();
      }

    } else {
      $this->tpl = "login";
      $this->infoToView = null;
      $this->display();
    }
  }

  /**
   *Creates view and sends relevant data
   */
  function display()
  {
    $view = new View($this->tpl, $this->infoToView);
  }

}

?>
