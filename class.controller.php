<?php

/**
 *Controller class handles input and other data
 */
class Controller
{

    /**
     * @var Model instance of model to be used in this class
     */
    private $model;
    /**
     * @var array combined POST & GET data received from client
     */
    private $input;

  public function __construct($input)
  {

      ChromePhp::info("-------- Next Page --------");
      ChromePhp::info("Input: " . json_encode($input));
      ChromePhp::info("Session: " . json_encode($_SESSION));

      $this->model = Model::getInstance();
      $this->input = $input;
      $this->infoToView = array();

      $this->handleLogic();


  }

  private function handleLogic()
  {
      //Handle input
      if (isset($this->input['type'])) {

          $template = null;

          switch ($this->input['type']) {
              case "login":
                  $template = $this->login();
                  break;
              case "booking":
                  $template = $this->booking();
                  break;
              case "register":
                  $template = $this->register();
                  break;
              case "logout":
                  $this->logout();
                  break;


              default:
                  session_destroy();
                  ChromePhp::error("Error: invalid type in input[] specified");
                  $template = "login";
                  $this->notify('A fehler occurred');
          }

          if($template != null)
          {
              $this->display($template);
          }

      } else {
          ChromePhp::info("No type specified!");

          if(isset($_SESSION['user']['name']) && isset($_SESSION['user']['pwd']))
          {
              // alread logged in!
              $name = $_SESSION['user']['name'];
              $pwd = $_SESSION['user']['pwd'];

              if($this->checkLogin($name, $pwd))
              {
                  ChromePhp::info("Relogin with valid user data");
                  $this->display("parent_dashboard");
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

          $this->display("login");
      }


      //Create User object
      if (isset($_SESSION['user']['id'])) {
          $this->user = User::fetchFromDB($_SESSION['user']['id']);
          ChromePhp::info("Userobject: " . $this->user);
      }
  }

    /**
     * Booking logic
     * @return string the template to be displayed
     */
    private function booking()
  {
      if ($this->input['booking']['action'] == "add") {
          $this->model->bookingAdd($this->input['booking']['slot'], $this->user->getId(), $this->input['booking']['teacher']);
      } elseif ($this->input['booking']['action'] == "delete") {
          $this->model->bookingDelete($this->input['booking']['slot'], $this->user->getId());
      }

      return "parent_dashboard";
  }

  /**
   * Logout logic
   * @return void
   */
    private function logout()
  {
      session_destroy();
      session_start();

      $_SESSION['logout'] = true; // notify about logout after reloading the page to delete all $_POST data

      header("Location: /");
  }
    /**
     * Login logic
     * @return string returns template to be displayed
     */
    private function login()
  {

      $input = $this->input;

      if(!isset($input['login']['user']) || !isset($input['login']['password']))
      {
          ChromePhp::info("No username || pwd in input[]");
          $this->notify('Kein Benutzername oder Passwort angegeben');
          return "login";
      }

      $pwd = $_SESSION['user']['pwd'] = $input['login']['password'];
      $usr = $_SESSION['user']['name'] = $input['login']['user'];

      if(isset($input['console'])) // used to only get raw login state -> can be used in js
      {
          die($this->checkLogin($usr, $pwd) ? "true" : "false");
      }

      if ($this->checkLogin($usr, $pwd)) {

          return "parent_dashboard";
      } else {

          ChromePhp::info("Invalid login data");
          $_SESSION['failed_login']['name'] = $usr;
          $this->notify('Benutzername oder Passwort falsch');
          return "login";
      }
  }

    /**
     * Register logic
     * @return string returns template to be displayed
     */
    private function register()
  {

      $input = $this->input;
      $model = $this->model;

      # check, then write into database, then login (session var...)
      $success = true;

      $notification = array();

      ChromePhp::info("-- Register --");

      //TODO: check duplicate email
      $username = $input['register']['usr'];
      $pwd = $input['register']['pwd'];
      $mail =  $input['register']['mail'];
      $students = $input['register']['student']; // format : ["name:bday", "name:bday", ...]

      ChromePhp::info("Username: " . $username);

      if ($model->usernameGetId($username) != null) {
          array_push($notification, "Dieser Benutzername ist bereits vergeben");
          $success = false;
      }

      $wrongStudentData = false;
      $pids = array();

      foreach ($students as $student)
      {
          $student = explode(":", urldecode($student));

          $name = $student[0];
          $bday = $student[1];

          $studentData = $model->checkPupilExist(explode(" ", $name)[0], explode(" ", $name)[1], $bday);
          $pid = $studentData["id"];
          $studentEid = $studentData["eid"];

          ChromePhp::info("Student: " . json_encode($name) . " born on " . $bday . " " . ($pid == null ? "does not exist" : "with id $pid and " . ($studentEid == null ? "no parents set" : "parent with id $studentEid")));

          if ($pid == null) {
              $wrongStudentData = true;
          } else if($studentEid != null)
          {
              array_push($notification, "Dem Schüler ".$name." ist bereits ein Elternteil zugeordnet"); //TODO: get student name with correct upper/lower case etc. from database (student object)
              $success = false;
          }
          else
          {
              array_push($pids, $pid);
          }

      }


      if ($wrongStudentData) {
          array_push($notification, "Bitte überprüfen Sie die angegebenen Schülerdaten");
          $success = false;
      }


      ChromePhp::info("Success: " . ($success == true ? "true" : "false"));

      if($success)
      {
          $userid = $model->registerParent($username, $pids, $mail, $pwd);
          $_SESSION['user']['id'] = $userid;

          $time = $_SESSION['user']['logintime'] = time();

          $_SESSION['user']['name'] = $username;
          $_SESSION['user']['pwd'] = $pwd;
          $_SESSION['user']['email'] = $mail;

          ChromePhp::info("Registered new user '$username' with id $userid and logged in @ $time");

      }

      if(isset($input['console'])) // used to only get raw registration response -> can be used in js
      {
          $output = array("success" => $success);
          if(sizeof($notification) != 0)
          {
              $output["notifications"] = $notification;
          }

          die(json_encode($output));

      }

      if ($success == true) {

          return "parent_dashboard";

      } else {

          if(sizeof($notification) != 0)
          {
              foreach ($notification as $item) {
                  $this->notify($item);
              }
          }
          return "login";
      }


  }

  /**
   *Creates view and sends relevant data
   * @param $template string the template to be displayed
   */
    private function display($template)
  {

      ChromePhp::info("Displaying 'templates/$template.php' with data " . json_encode($this->infoToView));

    $model = Model::getInstance();
   /* if ($template == "parent_dashboard" && isset($this->user)) {
      if ($this->user->getType() == 1) { // is parent/guardian

          /** @var Guardian $guardian
            $guardian = $this->user;
        $tchrs = $guardian->getTeachers();
        $schedule = [];

        foreach ($tchrs as $key => $tchrid) {
          $schedule = array_merge($schedule, array($tchrid => $model->teacherGetSlots($tchrid)));
        }
        $this->infoToView = array_merge($this->infoToView, array('parent_schedule' => $schedule));
      } elseif ($this->user->getType() == 2) { // is teacher

            /** @var Teacher $teacher
          $teacher = $this->user;
        $schedule = $model->teacherGetSlots($teacher->getId());
        $this->infoToView = array_merge($this->infoToView, array('teacher_schedule' => $schedule));
      }

      $userinfo = array('name' => $this->user->getName(), 'type' => $this->user->getType());
      $this->infoToView = array_merge($this->infoToView, array('user_info' => $userinfo));
    }*/
    new View($template, $this->infoToView);
  }


    /**
     * Displayes a materialized toast with specified message
     * @param string $message the message to display
     * @param int $time time to display
     */
  public function notify($message, $time = 4000)
  {
      if(!isset($this->infoToView))
          $this->infoToView = array();
      if(!isset($this->infoToView['notifications']))
          $this->infoToView['notifications'] = array();

      $notsArray = $this->infoToView['notifications'];

      array_push($notsArray, array("msg" => $message, "time" => $time));

      $this->infoToView['notifications'] = $notsArray;

  }



  private function checkLogin($usr, $pwd)
  { //TODO: get email from db and save to $_SESSION['user']['email']
      $model = Model::getInstance();
      if($model->passwordValidate($usr, $pwd)) {

          $uid = $_SESSION['user']['id'] = $model->usernameGetId($usr);
          if ($uid == null) {
              $this->notify("Database error!");
              $this->display("login");

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
