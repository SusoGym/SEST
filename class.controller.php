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
     * @param $input
     */
    public function __construct($input)
    {

        ChromePhp::info("-------- Next Page --------");
        ChromePhp::info("Input: " . json_encode($input));
        ChromePhp::info("Session: " . json_encode($_SESSION));

        if($this->model == null)
            $this->model = Model::getInstance();

        $this->input = $input;
        $this->infoToView = array();

        $this->handleLogic();


    }

    protected function handleLogic()
    {
        //Handle input
        if (isset($this->input['type'])) {

            $template = null;

            switch ($this->input['type']) {
                
				case "novell":
					//Nur zum testen verwendet - idealerweise wird beim Logincheck erkannt, ob Email Adresse oder LDAP Name eingegeben wurden
					$ldap="Suso"; 
					$pass=""; //real LDAP Data needed here
					$_SESSION['ldap'] = true;
					if(isset($this->model->checkNovellLogin($ldap,$pass)->{'code'}) == 200) {
					$_SESSION['user']['name'] = $this->model->checkNovellLogin($ldap,$pass)->{'name'};
					$_SESSION['user']['type'] = $this->model->checkNovellLogin($ldap,$pass)->{'type'};
					}
					break;
				case "lest": //Teacher chooses est
					$_SESSION['ldap']=null;
					self::$user = new Teacher(null,null,null,null,null,$_SESSION['user']['name']);
					if(isset($this->input['asgn']) ){
						$this->model->setAssignedSlot($this->input['asgn'],self::$user->getId());
						}
					
					$this->infoToView['deputat'] = self::$user->getLessonAmount();
					$this->infoToView['requiredSlots'] = self::$user->getRequiredSlots();
					$this->infoToView['user'] = self::$user;
					$this->infoToView['missing_slots'] = $missingSlots = self::$user->getMissingSlots();
					if($missingSlots == 0) {
						$this->infoToView['card_title'] = "Sprechzeiten am Elternsprechtag";
						$this->infoToView['slots_to_show'] = self::$user->getAssignedSlots();
						}
					else{
						$this->infoToView['card_title'] = "Festlegung der Sprechzeiten";
						$this->infoToView['slots_to_show'] = self::$user->getSlotListToAssign();
						}
					$this->infoToView['slotassignuntil'] = $this->model->getOptions()['slotassign'];
					
					$template ="tchr_slots";	
					break;
				case "home":
					if(isset($_SESSION['user']['type']) == "Teacher"){
						self::$user = new Teacher(null,null,null,null,null,$_SESSION['user']['name']);
						$this->infoToView['missing_slots'] = self::$user->getMissingSlots();
						$this->infoToView['slotassignuntil'] = $this->model->getOptions()['slotassign'];
						$template = $this->getDashboardName();
					}
					else {//which other usertypes are there?
						
					}
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
                    if($this->createUserObject() instanceof Admin)
                    {
                        $dashBoard = $_SESSION['board_type'];

                        if($dashBoard == 'parent')
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
		
        if (isset($_SESSION['user']['mail']) && isset($_SESSION['user']['pwd']) ) {
            // alread logged in!
            if (isset($_SESSION['user']['mail'])) {
				$email = $_SESSION['user']['mail'];
				$pwd = $_SESSION['user']['pwd'];
				

            if ($this->checkLogin($email, $pwd)) {
                ChromePhp::info("Relogin with valid user data");
                $this->display($this->getDashBoardName());
                return;
            } else {
                ChromePhp::info("Relogin with invalid user data. Redirecting to login page");
            }
			
			}
			

        }
		elseif (isset($_SESSION['ldap']) ){//Anpassung für Novell Login  - $_SESSION['user']['type'] kommt aus basicAuth data (json) 
				//Novell User logged in
				if(isset($_SESSION['user']['type']) ){
					//LDAP Login successful
					if($_SESSION['user']['type'] == "Teacher"){
					//teacher logged in
					self::$user = new Teacher(null,null,null,null,null,$_SESSION['user']['name']);
					$this->infoToView['missing_slots'] = self::$user->getMissingSlots();
					$this->infoToView['slotassignuntil'] = $this->model->getOptions()['slotassign'];
					$this->display($this->getDashBoardName());		
					}
				else {
					//pupil logged in
					$this->display($this->getDashBoardName());
					//Pupil class not yet adapted to create Object
					}	
				}
				else{
					//LDAP Login failed
					unset($_SESSION['ldap']);
					$this->notify('LDAP ERROR');
					$this->display("login");
				}
				
				return;
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
     * @param User $usr specify if object already created
     * @return User the current userobject
     */
    protected function createUserObject($usr = null)
    {
        if (isset($_SESSION['user']['id']) && (self::$user == null || self::$user->getId() != $_SESSION['user']['id'])) {
            self::$user = $usr == null ? Model::getInstance()->getUserById($_SESSION['user']['id']) : $usr;
            ChromePhp::info("Userobject: " . self::$user);
        }

        return self::getUser();
    }

    /**
     * Booking logic
     * @return string the template to be displayed
     */
    protected function booking()
    {
        if ($this->input['booking']['action'] == "add") {
            $this->model->bookingAdd($this->input['booking']['slot'], self::$user->getId(), $this->input['booking']['teacher']);
        } elseif ($this->input['booking']['action'] == "delete") {
            $this->model->bookingDelete($this->model->getAppointment($this->input['booking']['slot'], self::$user->getId()));
        }

        return $this->getDashBoardName();
    }

    /**
     * Logout logic
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
     * @return string returns template to be displayed
     */
    protected function login()
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
            return $this->getDashBoardName();
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

            return $this->getDashBoardName();

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
     * Returns the name of the correct dashboard
     * @return string
     */
    protected function getDashBoardName()
    {
        $this->createUserObject(); // create user obj if not already done
        $user = self::getUser();

				
        if($user instanceof Admin)
        {
            if(!isset($_SESSION['board_type']))
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
     * @param $template string the template to be displayed
     */
    protected function display($template)
    {
        $view = View::getInstance();
        $view->setDataForView($this->infoToView);
        $view->loadTemplate($template);
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
    protected function checkLogin($usr, $pwd)
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

	/**
	 * Adds new student as child to logged in parent
	 * @return bool success
	 */
	protected function addStudent()
	{
		$name = $this->input['name'];
		$bday = strtotime($this->input['bday']);
		$user = self::getUser();
		$uid = $user->getId();

    $student = $model->getStudentByName($name);

    if ($student == null) {
      array_push($notification, "Bitte überprüfen Sie die angegebenen Schülerdaten");
			return true;
    }

    $sid = $student->getId();
    $studentEid = $student->getEid();
    $name = $student->getSurname();
    $vorname = $student->getName();

    ChromePhp::info("Student: " . json_encode($name) . "($name, $vorname) born on " . $bday . " " . ($sid == null ? "does not exist" : "with id $pid and " . ($studentEid == null ? "no parents set" : "parent with id $studentEid")));

    if ($studentEid != null) {
      array_push($notification, "Dem Schüler ".$vorname." ".$name." ist bereits ein Elternteil zugeordnet");
			return true;
    }

		if ($this->model->parentAddStudent($sid) == false) {
			ChromePhp:info("Unexpected database error");
			return false;
		}
	}


}

?>
