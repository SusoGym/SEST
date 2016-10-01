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
            $_SESSION['user']['id'] = $model->usernameGetId();
            $_SESSION['user']['type'] = $model->userGetType($_SESSION['user']['id']);
            $this->tpl = "main";
    	      $this->infoToView = [];
    	      $this->display();
          } else {
            $this->tpl = "login";
    	      $this->infoToView = array('notifications' => array('Benutzername oder Passwort falsch'));
    	      $this->display();
          }
  	      break;

  	    case "booking":
  	      if ($input['booking']['action'] == "add") {
            $model->bookingAdd($input['booking']['slot'], $this->user->get_id(), $input['booking']['teacher']);
          } elseif ($input['booking']['action'] == "delete") {
            $model->bookingDelete($input['booking']['slot'], $this->user->get_id());
          }
          $this->tpl = "main";
          $this->infoToView = [];
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
    $model = new Model();
    if ($this->tpl == "main") {
      if ($this->user->get_type() == 1) {
        $tchrs = $this->user->get_teachers();
        $schedule = [];
        foreach ($tchrs as $key => $tchrid) {
          $schedule = array_merge($schedule, array($tchrid => $model->teacherGetSlots($tchrid)))
        }
        $this->infoToView = array_merge($this->infoToView, array('parent_schedule' => $schedule));
      } elseif ($this->user->get_type() == 2) {
        $schedule = $model->teacherGetSlots($this->user->id);
        $this->infoToView = array_merge($this->infoToView, array('teacher_schedule' => $schedule));
      }

      $userinfo = array('name' => $this->user->get_name(), 'type' => $this->user->get_type());
      $this->infoToView = array_merge($this->infoToView, array('user_info' => $userinfo));
    }
    $view = new View($this->tpl, $this->infoToView);
  }

}

?>
