<?php namespace administrator;

    /**
     *Controller class handles input and other data
     */
    class Controller extends \Controller
    {
        /**
         * @var string file name
         */
        private $file = null;

        /**
         * @var array
         */
        private $fileData = null;

        /**
         * @var string title/header of card
         */
        private $title = null;

        /**
         * @var string actiontype For view
         */
        private $actionType = null;

        /**
         * @var array menueItems for view
         */
        private $menueItems = array();

        /**
         * @var string backButton link
         */
        private $backButton = null;

        /**
         * @var array includes an array of all teachers of all forms to be transmitted to view
         */
        private $teachersOfForm = null;

        /**
         * @var array(int) all teacherIDs
         */
        private $allTeachers = null;

        /**
         * @var array(String) allForms
         */
        private $allForms = null;

        /**
         * @var string Klasse die bearbeitet wird
         */
        private $currentForm = null;

        /**
         * @var array eingerichtete Slots
         */
        private $existingSlots = null;

        /**
         * Konstruktor
         *
         * @param array
         */
        function __construct($input)
        {
            if (!isset($input['type']))
            {
                $input['type'] = "default";
            }

            $this->model = Model::getInstance();

            parent::__construct($input);


        }


        // --- Start overriding \Controller ---

        /**
         * Handles logic
         */
        protected function handleLogic()
        {


            $input = $this->input;

            $loggedIn = isset($_SESSION['user']['mail']) && isset($_SESSION['user']['pwd']) && $this->checkLogin($_SESSION['user']['mail'], $_SESSION['user']['pwd']) == 1;

            switch ($input['type'])
            {
                case 'logout':
                    $this->logout();
                    break;
                case 'login':
                    if (!$loggedIn)
                    {
                        $this->login();
                        break;
                    }
                default:
                    if ($loggedIn)
                    { // a.k.a logged in
                        $this->handleInput();
                    } else
                    {
                        $this->display("adminlogin");
                    }
                    break;
            }
        }

        /**
         * Handles login logic
         *
         * @param $input array input data
         * @return string template to be displayed
         */
        protected function login()
        {
            $input = $this->input;

            if (!isset($input['login']['mail']) || !isset($input['login']['password']))
            {
                \ChromePhp::info("No username || pwd in input[]");
                $this->notify('Kein Benutzername oder Passwort angegeben');

                return "adminlogin";
            }

            $pwd = $input['login']['password'];
            $usr = $input['login']['mail'];

            \ChromePhp::error("login()");
            $state = $this->checkLogin($usr, $pwd);

            \ChromePhp::info("Login Success: $state");

            if (isset($input['console']))
            { // used to only get raw login state -> can be used in js
                die(strval($state));
            }

            // things after here should not naturally happen

            if ($state == 1)
            {

                $this->title = "Startseite";

                return "main";
            } else if ($state == 2)
            {
                \ChromePhp::info("No Admin Permission");
                $this->notify('Ungenügende Berechtigung!');
            } else
            {
                $this->notify("Falsche Benutzername Passwort Kombination!");
            }

            return "adminlogin";
        }

        /**
         *Creates view and sends relevant data
         *
         * @param $template string
         */
        function display($template)
        {
            $view = \View::getInstance();
            $data = array();

            if (isset($_SESSION['dataForView']['notifications']))
            {
                foreach ($_SESSION['dataForView']['notifications'] as $not)
                {
                    $this->notify($not['msg'], $not['time']);
                }
                unset($_SESSION['dataForView']['notifications']);
            }

            $myDataForView =
                array("title"          => $this->title,
                      "action"         => $this->actionType,
                      "menueItems"     => $this->menueItems,
                      "backButton"     => $this->backButton,
                      "allteachers"    => $this->allTeachers,
                      "allForms"       => $this->allForms,
                      "teachersOfForm" => $this->teachersOfForm,
                      "currentForm"    => $this->currentForm,
                      "fileName"       => $this->file,
                      "fileData"       => $this->fileData,
                      "slots"          => $this->existingSlots
                );

            foreach ($myDataForView as $key => $value)
            {
                if ($value != null)
                    $data[$key] = $value;
            }

            $data = array_merge($data, array_merge($this->infoToView));
            $view->setDataForView($data);

            $view->loadTemplate($template);
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

            header("Location: ../");
        }

        /**
         *check login
         *
         * @param string $mail
         * @param string $pwd
         * @return int 1 => success, 2 => no permission, 0 => invalid login data
         */
        protected function checkLogin($mail, $pwd)
        {
            $model = Model::getInstance();
            if ($model->passwordValidate($mail, $pwd))
            {

                $usr = $model->getUserByMail($mail);
                if (($uid = $usr->getId()) == null)
                {
                    $this->notify("Database error!");
                    $this->display("adminlogin");

                    \ChromePhp::error("Unexpected database response! requested uid = null!");
                    exit();
                }
                $type = $usr->getType();

                //admin login MUST be type 0
                if ($type == 0)
                {

                    $_SESSION['user']['id'] = $uid;
                    $time = $_SESSION['user']['logintime'] = time();
                    $_SESSION['user']['pwd'] = $pwd;
                    $_SESSION['user']['mail'] = $mail;

                    $this->createUserObject($usr);

                    \ChromePhp::info("User '$mail' with id $uid of type $type successfully logged in @ $time");
                    unset($_SESSION['logout']);

                    return 1;
                } else
                {
                    return 2;
                }
            }

            return 0;
        }


        // --- End overriding \Controller ---


        /**
         *handles input data
         *
         * @param array $input
         */
        private function handleInput()
        {
            $input = $this->input;
            //Handle input
            switch ($input['type'])
            {
                //User Management
                case "usrmgt":
                    $this->title = "Benutzerverwaltung";
                    $this->display("usermgt");
                    break;
                //Settings
                case "settings":
                    $this->title = "Einstellungen";
                    $this->addMenueItem("?type=sestconfig", "Elternsprechtag konfigurieren");
                    $this->addMenueItem("?type=newsconfig", "Newsletter konfigurieren");
                    $this->addMenueItem("?type=options", "Optionen");
                    $this->display("simple_menue");
                    break;
                //Enter Newsletter
                case "news":
                    $this->title = "Newsletter eintragen";
                    $this->display("enternews");
                    break;
                //Select update options
                case "updmgt":
                    $this->title = "Datenabgleich";
                    $this->addMenueItem("?type=update_s", "Ableich Schülerdaten");
                    $this->addMenueItem("?type=update_t", "Abgleich Lehrerdaten");
		      $this->addMenueItem("?type=upload_e", "Upload Terminedatei");
                    $this->display("simple_menue");
                    break;
                //Update teacher data
                case "update_t":
                    //Einlesen der Lehrerdaten
                    $this->title = "Lehrerdaten abgleichen";
                    $this->actionType = "utchoose";
                    $this->display("update");
                    break;
                //Update student data
                case "update_s":
                    $this->title = "Schülerdaten abgleichen";
                    $this->actionType = "uschoose";
                    $this->display("update");
                    break;
		  //events file upload
		  case "upload_e":
		      $this->title = "Upload Terminedatei";
                    $this->actionType = "eventchoose";
                    $this->display("update");
                    break;
		 
		 
                //student file upload
		  case "utchoose":
                case "uschoose":
		  case "eventchoose":
		       $student = $input['type'] == "uschoose";
                    //von mir hinzugefügt
                    $input['type'] == "uschoose" ? $student = true : $student = false;

                    $upload = $this->fileUpload();
                    $success = $upload['success'];
                    $written = $success ? "true" : "false";
                    \ChromePhp::info($student ? "Student" : "Teacher" . " upload: $written");

                    if ($success)
                    {
                       echo  $_SESSION['file'] = $upload['location'];
                    }

                    if (isset($input['console']))
                    {
                        $error = (isset($upload['error']) ? $upload['error'] : "");

                        die("<script type='text/javascript'>window.top.window.uploadComplete($written, '$error');</script>");
                    }

                    if ($success)
                    {
                       
				echo "<script> alert($student);   </script>  ";
                       	 $this->title = "Datei upload zur Aktualisierung der " . $student ? "Schülerdaten" : "Lehrerdaten";
                       	 $this->prepareDataUpdate($student);
                        	$this->actionType = $student ? "usstart" : "utstart";
                        	$student ? $this->actionType = "usstart" : $this->actionType = "utstart";
                       	 echo $this->actionType;
                       	 $this->display("update1");
			 	  
                    } else
                    {
                        $this->display("update");
                    }
		  
                    break;
                case "dispsupdate1":
                case "disptupdate1":

                    $student = $input['type'] == "dispsupdate1";
                    //von mir hinzugefügt
                    $input['type'] == "dispsupdate1" ? $student = true : $student = false;
                    $this->title = "Datei upload zur Aktualisierung der " . $student ? "Schülerdaten" : "Lehrerdaten";
                    $this->prepareDataUpdate($student);
                    $this->actionType = $student ? "usstart" : "utstart";
                    $this->display("update1");
                    break;
                //Student/Teacher Update start
                case "usstart":
                case "utstart":
                    $input['type'] == "usstart" ? $student = true : $student = false;
                    //$student = $input['type'] == "usstart";

                    $this->title = $student ? "Schüler" : "Lehrer" . "daten aktualisiert";
                    $this->performDataUpdate($student, $input);
                    $this->display("update2");
                    break;

		 //
		case "dispupdateevents":
			$this->manageEvents();
			$this->title ="Termine";
			$this->display("events");
			break;	
                //SEST configuration
                case "sestconfig":
                    $this->title = "Konfiguration Elternsprechtag";
                    $this->addMenueItem("?type=setclasses", "Unterrichtszuordnung einrichten");
                    if (date('Ymd') <= $this->model->getOptions()['assignstart'])
                    {
                        $this->addMenueItem("?type=setslots", "Sprechzeiten einrichten");
                    }
                    $this->backButton = "?type=settings";
                    $this->display("simple_menue");
                    break;
                //News configuration
                case "newsconfig":
                    $this->title = "Konfiguration Newsletter (z.B. Emailversand/Anhänge etc.)";
                    $this->backButton = "?type=settings";
                    $this->display("simple_menue");
                    break;
                //Configure Options
                case "options":
                    if (isset($input['sbm']))
                    {

                        $this->model->updateOptions($_POST);
                    }
                    $this->title = "Konfiguration Optionen";
                    $this->backButton = "?type=settings";
                    $this->infoToView['options'] = $this->model->getOptionsForAdmin();
                    $this->display("options");
                    break;
                //Set SEST classes/teachers
                case "setclasses":
                    $this->allTeachers = $this->model->getTeachers();
                    $this->allForms = $this->model->getForms();
                    isset($input['teacher']) ? $t = $input['teacher'] : $t = null;
                    isset($input['update']) ? $u = $input['update'] : $u = null;
                    isset($input['form']) ? $f = $input['form'] : $f = null;
                    $this->classOperations($f, $u, $t);
                    $this->title = "Lehrer zu Klassen zuweisen";
                    $this->backButton = "?type=sestconfig";
                    $this->display("unterricht");
                    break;
                //Set SEST Slots
                case "setslots":
                    $this->title = "Sprechzeiten einrichten";
                    $this->backButton = "?type=sestconfig";
                    if (isset($input['del']))
                    {
                        $this->model->deleteSlot($input['del']);
                        //this->model->deleteBookableSlots($input['del']) - does not exist yet in model
                    }
                    if (isset($input['start']))
                    {
                        $slotId = $this->model->insertSlot($input['start'], $input['end']);
                        $this->model->createBookableSlots($slotId);
                    }
                    $this->existingSlots = $this->model->getSlots();
                    $this->display("slot_mgt");
                    break;
                //Set SEST Slots
                case "slots":

                    break;
                case "novell":
                    $this->model->checkNovellLogin("GrossA", "12345");
                    break;
                default:
                    $this->title = "Startseite";
                    unset($_SESSION['file']);
                    $this->display("main");
                    break;



            }
        }

        /**
         *uploading a file to server
         *
         * @return array[]
         */
        private function fileUpload()
        {

            $ret = array("success" => false);
            $success = false;
var_dump($_FILES);
            try
            {
                /*
                move_uploaded_file($_FILES['Datei']['tmp_name'], '/var/www/vhosts/suso.schulen.konstanz.de/httpdocs/_SusoIntern/uploadtemp/'.$_FILES['Datei']['name'])    )
                if(is_uploaded_file($_FILES['file']['tmp_name']) &&
                move_uploaded_file($_FILES['file']['tmp_name'], '/var/www/vhosts/suso.schulen.konstanz.de/httpdocs/_SusoIntern/uploadtemp/'.$_FILES['file']['name'])    )
                {
                  $this->file='/var/www/vhosts/suso.schulen.konstanz.de/httpdocs/_SusoIntern/uploadtemp/'.$_FILES['file']['name'];
                }*/

                $file = $_FILES['file'];

                if (isset($file['tmp_name']) && is_uploaded_file($file['tmp_name']) &&
                    move_uploaded_file($file['tmp_name'], './tmp/' . $file['name'])
                )
                {
                    $this->file = './tmp/' . $file['name'];
                    $ret['success'] = true;
                    $ret['location'] = './tmp/' . $file['name'];
                }
            } catch (\Exception $e)
            {
                $ret['error'] = $e->getMessage();
            } finally
            {
		return $ret;
            }
        }

        /**
         *prepare update of DB Data
         *
         * @param bool
         */
        private function prepareDataUpdate($student)
        {

            if (!isset($_SESSION['file']))
            {
                header("Location: /administrator"); //TODO: hardcoded ;-;
            } else if (!file_exists($_SESSION['file']))
            {
                $_SESSION['dataForView']['notifications'][] = array("msg" => "Invalid File Target!", "time" => 4000);
                header("Location: /administrator");
            }
            $fileHandler = new FileHandler($_SESSION['file']);
            $this->fileData[0] = $fileHandler->readHead();
            $this->fileData[1] = $fileHandler->readDBFields($student); //schueler=true
        }

        /**
         *perform update of DB Data
         *
         * @param bool
         * @param array $input (GET/POST Data)
         */
        private function performDataUpdate($student, $input)
        {
            if (!isset($_SESSION['file']))
            {
                header("Location: /administrator"); //TODO: hardcoded ;-;
            }

            $updateData = array();
            $fileHandler = new FileHandler($_SESSION['file']);
            $sourceHeads = $fileHandler->readHead();
            $x = 0;
            foreach ($sourceHeads as $h)
            {
                $updateData[] = array("source" => $h, "target" => $input['post_dbfield'][$x]);
                $x++;
            }
            $updateResults = $fileHandler->updateData($student, $updateData);    //gibt Anzahl eingefügter Zeilen an
            $this->fileData[0] = $updateResults[0];
            $this->fileData[1] = $updateResults[1];
            $this->fileData[2] = $fileHandler->deleteDataFromDB($student);
        }

	/**
	* Make Events and write ICS file
	*/
	private function manageEvents(){
	$filehandler = new FileHandler($_SESSION['file']);
	$events = $filehandler->readEventSourceFile();
	$tmanager = new TManager();	
	$tmanager->addToDB($events);
	//TO DO make ICS Files for staff and others
	//$tmanager->createICS(<FILENAME>,$events);
	//include filenames in cfg.ini
	}

        /**
         * Adds Menu Item
         *
         * @param $link string
         * @param $name string
         * @return void
         */
        private function addMenueItem($link, $name)
        {
            array_push($this->menueItems, array("link" => $link, "entry" => $name));
        }

        /**
         *set teacher class connections
         *
         * @param string $form
         * @param array(int) teacherIds
         */
        private function classOperations($form, $update, $teacher)
        {
            if (isset($update))
            {
                $this->model->setTeacherToForm($teacher, $update);
                $form = $update;
            }
            //read teachers in forms
            if (isset($form))
            {
                $this->currentForm = $form;
                $this->teachersOfForm = $this->model->getTeachersOfForm($form);
            }

        }
    }

?>
