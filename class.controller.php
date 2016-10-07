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
    if (isset($_SESSION['user']['type'])) {
        $usr_type = $_SESSION['user']['type'];
      if ($usr_type == 1) {
        $this->user = new Guardian(intval($_SESSION['user']['id']));
      } elseif ($usr_type) {
        $this->user = new Teacher(intval($_SESSION['user']['id']));
      }
        ChromePhp::info($this->user);
    }


    //Handle input
    if (isset($input['type'])) {
        ChromePhp::info("Type is: " . $input['type']);

      switch ($input['type']) {
        //Start login logic
        case "login":

            $this->tpl = "login";

            if(!isset($input['login']['user']) || !isset($input['login']['password']))
            {
                ChromePhp::info("No username || pwd in input[]");
                $this->notify('Kein Benutzername oder Passwort angegeben');
                $this->display();
                break;
            }

           if ($model->passwordValidate($usr = $input['login']['user'], $input['login']['password']) == true) {
               $uid = $_SESSION['user']['id'] = $model->usernameGetId($usr);

               if($uid == null)
               {
                   $this->notify("Database error!");
                   $this->display();

                   ChromePhp::error("Unexpected database response! requested uid = null!");
                   exit();
               }

               $type = $_SESSION['user']['type'] = $model->userGetType($uid);
               $time = $_SESSION['user']['logintime'] = time();

               ChromePhp::info("User '$usr' with id $uid of type $type successfully logged in @ $time");
               $this->tpl = "main";
               $this->infoToView = null;
    	       $this->display();
           } else {
               ChromePhp::info("Invalid login data");
               $this->notify('Benutzername oder Passwort falsch');
    	       $this->display();
           }
  	      break;
          // End login logic

        //Start booking logic
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
          // End booking logic
        // Start register logic
        case "register":
  	      # check, then write into database, then login (session var...)
  	      break;
          // End register logic
        // Start logout logic
  	    case "logout":
  	      session_destroy();

  	      $this->tpl = "login";
  	      $this->notify('Erfolgreich abgemeldet');
  	      $this->display();
  	      break;
          // End logout logic
  	    default:
  	      session_destroy();
          ChromePhp::error("Error: invalid type in input[] specified");
  	      $this->tpl = "login";
  	      $this->notify('A fehler occurred');
          $this->display();
      }

    } else {
        // TODO check if $_SESSION contains login
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


    /**
     * Displayes a materialized toast with specified message
     * @param string $message the message to display
     * @param int $time time to display
     */
  function notify($message, $time = 4000)
  {
      if(!isset($this->infoToView))
          $this->infoToView = array();
      if(!isset($this->infoToView['notifications']))
          $this->infoToView['notifications'] = array();

      $notsArray = $this->infoToView['notifications'];

      array_push($notsArray, array("msg" => $message, "time" => $time));

      $this->infoToView['notifications'] = $notsArray;
      ChromePhp::info("Notifications: " . json_encode($notsArray));

  }



}

?>
