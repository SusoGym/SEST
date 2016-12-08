<?php

    /**
     *Controller class handles input and other data
     */
    class Controller
    {

        /**
         * @var Model instance of model to be used in this class
         */
        protected $model;
        /**
         * @var array combined POST & GET data received from client
         */
        protected $input;

        /**
         * @var User
         */
        protected static $user;

        /**
         * @return User
         */
        public static function getUser()
        {
            return self::$user;
        }

        /**
         * Controller constructor.
         *
         * @param $input
         */
        public function __construct($input)
        {

            ChromePhp::info("-------- Next Page --------");
            ChromePhp::info("Input: " . json_encode($input));
            ChromePhp::info("Session: " . json_encode($_SESSION));

            if ($this->model == null)
                $this->model = Model::getInstance();

            $this->input = $input;
            $this->infoToView = array();

            $this->handleLogic();


        }

        protected function handleLogic()
        {


            if (isset($_SESSION['user']['mail']) && isset($_SESSION['user']['pwd']))
            { // Check if already logged-in and creates userobj
                $email = $_SESSION['user']['mail'];
                $pwd = $_SESSION['user']['pwd'];

                if (!$this->checkLogin($email, $pwd))
                {
                    unset($_SESSION['user']);
                    ChromePhp::info("Tried to log in with invalid userdata");
                }

            }
            if (self::getUser() instanceof Admin) // Admin has no actual use of the normal view does he?
                header("Location: /administrator");

            if (isset($this->input['type']))
            { // handle type if allowed
                $type = $this->input['type'];
                $template = null;
                if ($type == 'login' || $type == 'logout' || isset(self::$user) || isset($this->input['console']))
                    $template = $this->handleType();

                if ($template != null)
                {
                    $this->display($template);

                    return;
                }
            }

            if (!isset($this->input['type']) && !isset(self::$user))
                ChromePhp::info("No type nor userobj set!");
            else if (!isset($this->input['type']))
                ChromePhp::info("No type set!");
            else if (!isset(self::$user))
                ChromePhp::info("No userobj set!");


            if (isset($_SESSION['logout']))
            {
                unset($_SESSION['logout']);
                $this->notify('Erfolgreich abgemeldet');
            }


            if (isset(self::$user))
                $this->display($this->getDashBoardName());
            else
                $this->display("login");

        }

        /**
         * @return string
         */
        protected function handleType()
        {
            $template = null;
            switch ($this->input['type'])
            {
                case "lest": //Teacher chooses est
                    $_SESSION['ldap'] = null;

                    if (isset($this->input['asgn']))
                    {
                        $this->model->setAssignedSlot($this->input['asgn'], self::$user->getId());
                    }
                    /** @var Teacher $teacher */
                    $teacher = self::$user;
                    $this->infoToView['deputat'] = $teacher->getLessonAmount();
                    $this->infoToView['requiredSlots'] = $teacher->getRequiredSlots();
                    $this->infoToView['user'] = $teacher;
                    ($missingSlots = $teacher->getMissingSlots() > 0) ? $missingSlots = $missingSlots : $missingSlots = 0;
                    $this->infoToView['missing_slots'] = $teacher->getMissingSlots();
                    $this->infoToView['card_title'] = "Sprechzeiten am Elternsprechtag";
                    if ($missingSlots == 0)
                    {
                        $this->infoToView['card_title'] = "Sprechzeiten am Elternsprechtag";
                        $this->infoToView['slots_to_show'] = $teacher->getAssignedSlots();
                    } else
                    {
                        $this->infoToView['card_title'] = "Festlegung der Sprechzeiten";
                        $this->infoToView['slots_to_show'] = $teacher->getSlotListToAssign();
                    }
                    $this->infoToView['slots_to_show'] = $teacher->getSlotListToAssign();
                    $this->infoToView['assign_end'] = $this->model->getOptions()['assignend'];
                    $this->infoToView['assign_start'] = $this->model->getOptions()['assignstart'];

                    $template = "tchr_slots";
                    break;
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
                case "changeDashboard":
                    if (self::getUser() instanceof Admin)
                    {
                        $dashBoard = $_SESSION['board_type'];

                        if ($dashBoard == 'parent')
                            $dashBoard = 'teacher';
                        else
                            $dashBoard = 'parent';

                        $_SESSION['board_type'] = $dashBoard;
                    }
                    break;
                case "addStudent":
                    $this->addStudent();
                    break;
                default:
                    if (self::$user instanceof Teacher)
                    {
                        /** @var Teacher $user */
                        $user = self::$user;
                        $this->infoToView['missing_slots'] = $user->getMissingSlots();
                        $this->infoToView['assign_end'] = $this->model->getOptions()['assignend'];
                        $this->infoToView['assign_start'] = $this->model->getOptions()['assignstart'];
                    }
                    break;
            }

            return $template;
        }

        /**
         * Creates userobject of logged in user and saves it to Controller:$user
         *
         * @param User $usr specify if object already created
         * @return User the current userobject
         */
        protected function createUserObject($usr = null)
        {

            if (self::$user != null)
                return self::getUser();

            $id = $_SESSION['user']['id'];

            if (isset($_SESSION['user']['isTeacher']) && isset($_SESSION['user']['id']))
            {
                self::$user = (($usr == null || !($usr instanceof Teacher)) ? Model::getInstance()->getTeacherByTeacherId($id) : $usr);
            } else if (isset($_SESSION['user']['id']) && (self::$user == null || self::$user->getId() != $_SESSION['user']['id']))
            {
                self::$user = ($usr == null ? Model::getInstance()->getUserById($id) : $usr);
            }

            ChromePhp::info("Userobject: " . self::$user);

            return self::getUser();
        }

        /**
         * Booking logic
         *
         * @return string the template to be displayed
         */
        protected function booking()
        {
            if ($this->input['booking']['action'] == "add")
            {
                $this->model->bookingAdd($this->input['booking']['slot'], self::$user->getId(), $this->input['booking']['teacher']);
            } elseif ($this->input['booking']['action'] == "delete")
            {
                $this->model->bookingDelete($this->model->getAppointment($this->input['booking']['slot'], self::$user->getId()));
            }

            return $this->getDashBoardName();
        }

        /**
         * Logout logic
         *
         * @return void
         */
        protected function logout()
        {
            session_destroy();
            session_start();

            $_SESSION['logout'] = true; // notify about logout after reloading the page to delete all $_POST data

            header("Location: /");
        }

        /**
         * Login logic
         *
         * @return string returns template to be displayed
         */
        protected function login()
        {

            $input = $this->input;

            if (!isset($input['login']['mail']) || !isset($input['login']['password']))
            {
                ChromePhp::info("No mail || pwd in input[]");
                $this->notify('Keine Email-Addresse oder Passwort angegeben');

                return "login";
            }

            $pwd = $input['login']['password'];
            $mail = $input['login']['mail'];

            if (isset($input['console'])) // used to only get raw login state -> can be used in js
            {
                die($this->checkLogin($mail, $pwd) ? "true" : "false");
            }

            if ($this->checkLogin($mail, $pwd))
            {
                return $this->getDashBoardName();
            } else
            {

                ChromePhp::info("Invalid login data");
                $this->notify('Email-Addresse oder Passwort falsch');

                return "login";
            }
        }

        /**
         * Register logic
         *
         * @return string returns template to be displayed
         */
        protected function register()
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

            if (($userObj = $model->getUserByMail($mail)) != null)
            {
                $id = $userObj->getId();
                array_push($notification, "Diese Email-Addresse ist bereits registriert.");
                ChromePhp::info("Email bereits registriert mit id $id");
                $success = false;
            }

            $wrongStudentData = false;
            $pids = array();

            foreach ($students as $student)
            {
                $student = explode(":", urldecode($student));

                $name = $student[0];
                $bday = $student[1];

                $studentObj = $model->getStudentByName($name);

                if ($studentObj == null)
                {
                    $wrongStudentData = true;
                    continue;
                }

                $pid = $studentObj->getId();
                $studentEid = $studentObj->getEid();
                $name = $studentObj->getSurname();
                $vorname = $studentObj->getName();

                ChromePhp::info("Student: " . json_encode($name) . "($name, $vorname) born on " . $bday . " " . ($pid == null ? "does not exist" : "with id $pid and " . ($studentEid == null ? "no parents set" : "parent with id $studentEid")));

                if ($studentEid != null)
                {
                    array_push($notification, "Dem Schüler $vorname $name ist bereits ein Elternteil zugeordnet");
                    $success = false;
                } else
                {
                    array_push($pids, $pid);
                }

            }


            if ($wrongStudentData)
            {
                array_push($notification, "Bitte überprüfen Sie die angegebenen Schülerdaten");
                $success = false;
            }


            ChromePhp::info("Success: " . ($success == true ? "true" : "false"));

            if ($success)
            {
                $userid = $model->registerParent($pids, $mail, $pwd);

                $this->checkLogin($mail, $pwd);

            }

            if (isset($input['console'])) // used to only get raw registration response -> can be used in js
            {
                $output = array("success" => $success);
                if (sizeof($notification) != 0)
                {
                    $output["notifications"] = $notification;
                }

                die(json_encode($output));

            }

            if ($success == true)
            {

                return $this->getDashBoardName();

            } else
            {

                if (sizeof($notification) != 0)
                {
                    foreach ($notification as $item)
                    {
                        $this->notify($item);
                    }
                }

                return "login";
            }


        }

        /**
         * Returns the name of the correct dashboard
         *
         * @return string
         */
        protected function getDashBoardName()
        {
            $this->createUserObject(); // create user obj if not already done
            $user = self::getUser();


            if ($user instanceof Admin)
            {
                if (!isset($_SESSION['board_type']))
                {
                    $_SESSION['board_type'] = 'parent';
                }

                return $_SESSION['board_type'] . '_dashboard';
            } else if ($user instanceof Teacher)
            {
                return "teacher_dashboard";
            } else
            {
                return "parent_dashboard";
            }

        }

        /**
         *Creates view and sends relevant data
         *
         * @param $template string the template to be displayed
         */
        protected function display($template)
        {
            $view = View::getInstance();
            $this->infoToView['usr'] = self::$user;
            $view->setDataForView($this->infoToView);
            $view->loadTemplate($template);
        }


        /**
         * Displayes a materialized toast with specified message
         *
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
         * @param $email string user name
         * @param $pwd string user pwd
         * @return bool success of login
         */
        protected function checkLogin($email, $pwd)
        {
            // mechanism to only verify login data every 60 sec!

            $time = isset($_SESSION['user']['logintime']) ? $_SESSION['user']['logintime'] : 0;
            $timeGone = time() - $time;
            $inTime = $timeGone <= 60; // last login check was 60 sec or less ago

            ChromePhp::info("Time since last login verification: $timeGone");
            if ($inTime)
                ChromePhp::info("Skipping login verification");
            else
                ChromePhp::info("Login timed out! Initiating new verification!");

            // end mechanism

            $model = Model::getInstance();
            $success = $inTime;
            $uid = null;
            $type = null;

            if ($email == 'teacher@teacher' && DEBUG) // test account
                $email = 'test@suso.schulen.konstanz.de';

            if ($model->passwordValidate($email, $pwd) && !$inTime)
            {
                $userObj = $model->getUserByMail($email);
                if ($userObj != null)
                {
                    $type = $userObj->getType();
                    $uid = $_SESSION['user']['id'] = $userObj->getId();
                    $time = $_SESSION['user']['logintime'] = time();

                    $success = true;
                }
            } else if (explode('@', $email)[1] == "suso.schulen.konstanz.de" && !$inTime)
            {
                $userObj = $model->getTeacherByEmailAndLdapPwd($email, $pwd);
                if ($userObj == null)
                {
                    // nope
                    $success = false;
                } else
                {
                    $type = $userObj->getType();
                    $uid = $_SESSION['user']['id'] = $userObj->getId();
                    $_SESSION['user']['isTeacher'] = true;
                    $success = true;
                }

            } else if ($inTime)
            {
                $this->createUserObject();
                $type = self::getUser()->getType();
                $id = self::getUser()->getId();
            }

            if (!$success)
            {
                ChromePhp::info("Invalid login data");
            } else
            {

                $_SESSION['user']['mail'] = $email;
                $_SESSION['user']['pwd'] = $pwd;

                $this->createUserObject();

                if (!$inTime)
                {
                    $time = $_SESSION['user']['logintime'] = time();
                    ChromePhp::info("User '$email' with id $uid of type $type successfully logged in @ $time");
                }

            }

            return $success;
        }

        /**
         * Adds new student as child to logged in parent
         *
         * @return bool success
         */
        protected function addStudent()
        {

            if (!isset(self::$user) || !(self::$user instanceof Guardian))
                return false;

            $name = $this->input['name'];
            $bday = strtotime($this->input['bday']);
            /** @var Guardian $user */
            $user = self::getUser();
            $pid = $user->getParentId();
            $model = Model::getInstance();

            $student = $model->getStudentByName($name);

            if ($student == null)
            {
                array_push($notification, "Bitte überprüfen Sie die angegebenen Schülerdaten");

                return true;
            }

            $sid = $student->getId();
            $studentEid = $student->getEid();
            $name = $student->getSurname();
            $vorname = $student->getName();

            ChromePhp::info("Student: " . json_encode($name) . "($name, $vorname) born on " . $bday . " " . ($sid == null ? "does not exist" : "with id $pid and " . ($studentEid == null ? "no parents set" : "parent with id $studentEid")));

            if ($studentEid != null)
            {
                array_push($notification, "Dem Schüler " . $vorname . " " . $name . " ist bereits ein Elternteil zugeordnet");

                return true;
            }

            if (Model::getInstance()->parentAddStudent($pid, $sid) == false)
            {
                ChromePhp::info("Unexpected database error");

                return false;
            }
        }


    }

?>
