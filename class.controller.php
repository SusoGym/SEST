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

    /**
     * @var User
     */
    private static $user;

    /**
     * @return User
     */
    public static function getUser()
    {
        return self::$user;
    }

    /**
     * Controller constructor.
     * @param $input
     */
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
                    $template = "login";
                    break;
                default:

                    break;
            }

            if ($template != null) {

                //Create User object
                $this->createUserObject();

                $this->display($template);
                return;
            }

        }
        ChromePhp::info("No type specified!");

        if (isset($_SESSION['user']['mail']) && isset($_SESSION['user']['pwd'])) {
            // alread logged in!
            $email = $_SESSION['user']['mail'];
            $pwd = $_SESSION['user']['pwd'];

            if ($this->checkLogin($email, $pwd)) {
                ChromePhp::info("Relogin with valid user data");
                $this->display("parent_dashboard");
                return;
            } else {
                ChromePhp::info("Relogin with invalid user data. Redirecting to login page");
            }

        }

        if (isset($_SESSION['logout'])) {
            unset($_SESSION['logout']);
            $this->notify('Erfolgreich abgemeldet');
        }

        $this->display("login");


        //Create User object
        $this->createUserObject();
    }

    /**
     * Creates userobject of logged in user and saves it to Controller:$user
     */
    private function createUserObject()
    {
        if (isset($_SESSION['user']['id']) && (self::$user == null || self::$user->getId() != $_SESSION['user']['id'])) {
            self::$user = Model::getInstance()->getUserById($_SESSION['user']['id']);
            ChromePhp::info("Userobject: " . self::$user);
        }
    }

    /**
     * Booking logic
     * @return string the template to be displayed
     */
    private function booking()
    {
        if ($this->input['booking']['action'] == "add") {
            $this->model->bookingAdd($this->input['booking']['slot'], self::$user->getId(), $this->input['booking']['teacher']);
        } elseif ($this->input['booking']['action'] == "delete") {
            $this->model->bookingDelete($this->model->getAppointment($this->input['booking']['slot'], self::$user->getId()));
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

        if (!isset($input['login']['mail']) || !isset($input['login']['password'])) {
            ChromePhp::info("No mail || pwd in input[]");
            $this->notify('Keine Email-Addresse oder Passwort angegeben');
            return "login";
        }

        $pwd = $_SESSION['user']['pwd'] = $input['login']['password'];
        $mail = $_SESSION['user']['mail'] = $input['login']['mail'];

        if (isset($input['console'])) // used to only get raw login state -> can be used in js
        {
            die($this->checkLogin($mail, $pwd) ? "true" : "false");
        }

        if ($this->checkLogin($mail, $pwd)) {
            return "parent_dashboard";
        } else {

            ChromePhp::info("Invalid login data");
            $this->notify('Email-Addresse oder Passwort falsch');
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

        $pwd = $input['register']['pwd'];
        $mail = $input['register']['mail'];
        $students = $input['register']['student']; // format : ["name:bday", "name:bday", ...]

        ChromePhp::info("Email: " . $mail);

        if (($userObj = $model->getUserByMail($mail)) != null) {
            $id = $userObj->getId();
            array_push($notification, "Diese Email-Addresse ist bereits registriert.");
            ChromePhp::info("Email bereits registriert mit id $id");
            $success = false;
        }

        $wrongStudentData = false;
        $pids = array();

        foreach ($students as $student) {
            $student = explode(":", urldecode($student));

            $name = $student[0];
            $bday = $student[1];

            $studentObj = $model->getStudentByName($name);

            if ($studentObj == null) {
                $wrongStudentData = true;
                continue;
            }

            $pid = $studentObj->getId();
            $studentEid = $studentObj->getEid();
            $name = $studentObj->getSurname();
            $vorname = $studentObj->getName();

            ChromePhp::info("Student: " . json_encode($name) . "($name, $vorname) born on " . $bday . " " . ($pid == null ? "does not exist" : "with id $pid and " . ($studentEid == null ? "no parents set" : "parent with id $studentEid")));

            if ($studentEid != null) {
                array_push($notification, "Dem Schüler $vorname $name ist bereits ein Elternteil zugeordnet");
                $success = false;
            } else {
                array_push($pids, $pid);
            }

        }


        if ($wrongStudentData) {
            array_push($notification, "Bitte überprüfen Sie die angegebenen Schülerdaten");
            $success = false;
        }


        ChromePhp::info("Success: " . ($success == true ? "true" : "false"));

        if ($success) {
            $userid = $model->registerParent($pids, $mail, $pwd);

            $this->checkLogin($mail, $pwd);

        }

        if (isset($input['console'])) // used to only get raw registration response -> can be used in js
        {
            $output = array("success" => $success);
            if (sizeof($notification) != 0) {
                $output["notifications"] = $notification;
            }

            die(json_encode($output));

        }

        if ($success == true) {

            return "parent_dashboard";

        } else {

            if (sizeof($notification) != 0) {
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
        if (!isset($this->infoToView))
            $this->infoToView = array();
        if (!isset($this->infoToView['notifications']))
            $this->infoToView['notifications'] = array();

        $notsArray = $this->infoToView['notifications'];

        array_push($notsArray, array("msg" => $message, "time" => $time));

        $this->infoToView['notifications'] = $notsArray;

    }


    /**
     * @param $usr string user name
     * @param $pwd string user pwd
     * @return bool success of login
     */
    private function checkLogin($usr, $pwd)
    {
        $model = Model::getInstance();
        if ($model->passwordValidate($usr, $pwd)) {

            $userObj = $model->getUserByMail($usr);
            if ($userObj == null) {
                $this->notify("Falsche Anmeldedaten!");
                $this->display("login");

                //ChromePhp::error("Unexpected database response! requested uid = null!");
                return false;
            }


            $type = $userObj->getType();
            $uid = $_SESSION['user']['id'] = $userObj->getId();
            $this->createUserObject();
            $time = $_SESSION['user']['logintime'] = time();

            ChromePhp::info("User '$usr' with id $uid of type $type successfully logged in @ $time");

            return true;
        }

        //TODO: validate login by username xor norvell(for teachers etc.)

        return false;
    }


}

?>
