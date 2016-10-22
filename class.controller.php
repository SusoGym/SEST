<?php

/**
 *Controller class handles input and other data
 */
class Controller
{

  function __construct($input)
  {

      ChromePhp::info("-------- Next Page --------");
      ChromePhp::info("Input: " . json_encode($input));
      ChromePhp::info("Session: " . json_encode($_SESSION));

      $model = Model::getInstance();
      $this->infoToView = array();

    //Handle input
    if (isset($input['type'])) {

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

            $pwd = $_SESSION['user']['pwd'] = $input['login']['password'];
            $usr = $_SESSION['user']['name'] = $input['login']['user'];

            if(isset($input['console'])) // used to only get raw login state -> can be used in js
            {
                die($this->login($usr, $pwd) ? "true" : "false");
            }

           if ($this->login($usr, $pwd)) {

               $this->tpl = "main";
               $this->display();
           } else {

               ChromePhp::info("Invalid login data");                 // eigentlich sollte man das mit js machen, damit Seite bei (fehlerhaft) anmelden nicht neu läd.....
               $_SESSION['failed_login']['name'] = $usr;
               $this->notify('Benutzername oder Passwort falsch');
    	       $this->display();
           }
  	      break;
          // End login logic

        //Start booking logic
  	    case "booking":
  	      if ($input['booking']['action'] == "add") {
            $model->bookingAdd($input['booking']['slot'], $this->user->getId(), $input['booking']['teacher']);
          } elseif ($input['booking']['action'] == "delete") {
            $model->bookingDelete($input['booking']['slot'], $this->user->getId());
          }
          $this->tpl = "main";
          $this->display();
          break;
          // End booking logic
        // Start register logic
        case "register":
  	      # check, then write into database, then login (session var...)
          $success = true;

          ChromePhp::info("-- Register --");

          $username = $input['register']['username'];
          ChromePhp::info("Username: " . $username);

          if ($model->usernameGetId($username) != null) {
            $this->notify("Dieser Benutzername ist bereits vergeben");
            $success = false;
          }

          //TODO: check duplicate email
          //TODO: check valid email

          $rawName = $input['register']['student'];
          $pname = explode(" ", $rawName);     //TODO: multiple pupil instances
          $bday = $input['register']['pday'];
          $pwd = $input['register']['pwd'];
          $pwdrep = $input['register']['pwdrep'];
          $mail = $input['register']['mail'];

          ChromePhp::info("Input: " . json_encode(array("student" => $pname, "student_bday" => $bday, "mail" => $mail, "pwd" => $pwd, "pwdrep" => $pwdrep)));

          $studentData = $model->checkPupilExist($pname[0], $pname[1], $bday); //TODO: surname middle_name last_name || return int[] with multiple pupils

          $pid = $studentData["id"];
          $studentEid = $studentData["eid"];

            ChromePhp::info("Student: " . json_encode($pname) . " born on " . $bday . " " . ($pid == null ? "does not exist" : "with id $pid and " . ($studentEid == null ? "no parents set" : "parent with id $studentEid")));

          //TODO: check if pupil alread has parent

          if ($success && $pid == null) {
              $this->notify("Bitte überprüfen Sie die angegebenen Schülerdaten");
              $success = false;
          }

          if($success && $studentEid != null)
          {
              $this->notify("Dem Schüler $rawName ist bereits ein Elternteil zugeordnet!"); //TODO: get student name with correct upper/lower case etc. from database (student object)
              $success = false;
          }

          if ($success && $pwd != $pwdrep)  //TODO: don't check this with php but with javascript?
          {
              ChromePhp::info("Passwords not identical");
            $this->notify("Die Passwörter stimmen nicht überein");
            $success = false;
          }
          else if($pwd == $pwdrep)
          {
              ChromePhp::info("Passwords identical");
          }

          ChromePhp::info("Success: " . ($success == true ? "true" : "false"));

          if ($success == true) {
            $userid = $model->registerParent($username, $pid, $mail, $pwd);
            $_SESSION['user']['id'] = $userid;

              $time = $_SESSION['user']['logintime'] = time();

              ChromePhp::info("Registered new user '$username' with id $userid and logged in @ $time");

              $this->tpl = "main";
              $this->display();


          } else {
            $this->tpl = "login";
            $this->display();
          }


  	      break;
          // End register logic
        // Start logout logic
  	    case "logout":
  	      session_destroy();
          session_start();

          $_SESSION['logout'] = true; // notify about logout after reloading the page to delete all $_POST data

          header("Location: /");
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
        ChromePhp::info("No type specified!");

        if(isset($_SESSION['user']['name']) && isset($_SESSION['user']['pwd']))
        {
            // alread logged in!
            $name = $_SESSION['user']['name'];
            $pwd = $_SESSION['user']['pwd'];

            if($this->login($name, $pwd))
            {
                ChromePhp::info("Relogin with valid user data");
                $this->tpl = "main";
                $this->display();
                return;
            }
            else
            {
                ChromePhp::info("Relogin with invalid user data. Redirecting to login page");
            }

        }

        if(isset($_SESSION['logout']))
        {
            unset($_SESSION['logout']);
            $this->notify('Erfolgreich abgemeldet');
        }

        $this->tpl = "login";
        $this->display();
    }


    //Create User object
    if (isset($_SESSION['user']['id'])) {
      $this->user = User::fetchFromDB($_SESSION['user']['id']);
      ChromePhp::info("Userobject: " . $this->user);
    }


  }

  /**
   *Creates view and sends relevant data
   */
  function display()
  {
      ChromePhp::info("Displaying 'templates/" . $this->tpl . ".php' with data " . json_encode($this->infoToView));

    $model = Model::getInstance();
    if ($this->tpl == "main" && isset($this->user)) {
      if ($this->user->getType() == 1) { // is parent/guardian

          /** @var Guardian $guardian */
            $guardian = $this->user;
        $tchrs = $guardian->getTeachers();
        $schedule = [];

        foreach ($tchrs as $key => $tchrid) {
          $schedule = array_merge($schedule, array($tchrid => $model->teacherGetSlots($tchrid)));
        }
        $this->infoToView = array_merge($this->infoToView, array('parent_schedule' => $schedule));
      } elseif ($this->user->getType() == 2) { // is teacher

            /** @var Teacher $teacher */
          $teacher = $this->user;
        $schedule = $model->teacherGetSlots($teacher->getId());
        $this->infoToView = array_merge($this->infoToView, array('teacher_schedule' => $schedule));
      }

      $userinfo = array('name' => $this->user->getName(), 'type' => $this->user->getType());
      $this->infoToView = array_merge($this->infoToView, array('user_info' => $userinfo));
    }
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

  }



  function login($usr, $pwd)
  {
      $model = Model::getInstance();
      if($model->passwordValidate($usr, $pwd)) {

          $uid = $_SESSION['user']['id'] = $model->usernameGetId($usr);
          if ($uid == null) {
              $this->notify("Database error!");
              $this->display();

              ChromePhp::error("Unexpected database response! requested uid = null!");
              exit();
          }

          $type = $model->userGetType($uid);
          $time = $_SESSION['user']['logintime'] = time();

          ChromePhp::info("User '$usr' with id $uid of type $type successfully logged in @ $time");

          return true;
      }

      return false;
  }


}

?>
