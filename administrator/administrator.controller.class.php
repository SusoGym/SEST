<?php namespace administrator;

/**
 *Controller class handles input and other data
 */
class Controller
{
    /**
     * @var string file name
     */
    private $file=null;
	
	/**
	* @var array 
	*/
	private $fileData=null;

    /**
     * @var array data - various data to be used in view
     */
    private $dataForView = array();

    /**
	*@var string title/header of card 
	*/
	private $title=null;
	
	/**
	*@var string actiontype For view
	*/
	private $actionType=null;
	
	/**
	*@var array menueItems for view
	*/
	private $simpleMenueItems=null;
	
	/**
	*@var astring backButton link
	*/
	private $backButton=null;
	
	/**
	*@var array includes an array of all teachers of all forms to be transmitted to view
	*/
	private $teachersOfForm=null;
	
	/**
	*@var array(int) all teacherIDs
	*/
	private $allTeachers=null;
	
	/**
	*@var array(String) allForms
	*/
	private $allForms=null;
	
	/**
	*@var string Klasse die bearbeitet wird
	*/
	private $currentForm=null;

    /**
     *Konstruktor
     * @param array
     */
    function __construct($input)
    {


        ChromePhp::info("-------- Next Page --------");
        ChromePhp::info("Input: " . json_encode($input));
        ChromePhp::info("Session: " . json_encode($_SESSION));

        $this->model = Model::getInstance();
        $this->handleLogic($input);

    }


    private function handleLogic($input)
    {

        if(!isset($input['type']))
        {
            $input['type'] = "default";
        }

        if(!isset($_SESSION['user']['mail']) || !isset($_SESSION['user']['pwd']))
        {
            unset($_SESSION['user']);
        }


            switch ($input['type']) {
                case 'login':
                    $this->handleLogin($input);
                    break;
                case 'logout':
                    $this->logout();
                    break;
                default:
                    if (isset($_SESSION['user']['mail']) && isset($_SESSION['user']['pwd']) && $this->login($_SESSION['user']['mail'], $_SESSION['user']['pwd']) == 1) { // a.k.a logged in
                        $this->handleInput($input);
                    } else {
                        $this->display("adminlogin");
                    }
                    break;
            }
    }


    /**
     *handles input data
     * @param array $input
     */
    private function handleInput($input)
    {
        //Handle input
            switch ($input['type']) {
                //User Management
                case "usrmgt":
                    $this->title = "Benutzerverwaltung";
                    $this->display("usermgt");
                    break;
                //Settings
                case "settings":
                    $this->title="Einstellungen";
					$this->simpleMenueItems[0]=array("link"=>"index.php?type=sestconfig","entry"=>"Elternsprechtag konfigurieren");
					$this->simpleMenueItems[1]=array("link"=>"index.php?type=newsconfig","entry"=>"Newsletter konfigurieren");
					$this->display("simple_menue");
					break;
                //Enter Newsletter
                case "news":
                    $this->title = "Newsletter eintragen";
                    $this->display("enternews");
                    break;
                //Select update options
				case "updmgt":
					$this->title="Datenabgleich";
					$this->simpleMenueItems[0]=array("link"=>"index.php?type=update_s","entry"=>"Abgleich Schülerdaten");
					$this->simpleMenueItems[1]=array("link"=>"index.php?type=update_t","entry"=>"Abgleich Lehrerdaten");
					$this->display("simple_menue");
					break;
                //Update teacher data
                case "update_t":
                    //Einlesen der Lehrerdaten
                    $this->title="Lehrerdaten abgleichen";
					$this->actionType="utchoose";
					$this->display("update");
                    break;
                //Update student data
                case "update_s":
                    $this->title = "Schülerdaten abgleichen";
					$this->actionType="uschoose";
					$this->display("update");		
                    break;
                //student file upload
                case "utchoose":
                case "uschoose":
                    $student = $input['type'] == "uschoose";
					//von mir hinzugefügt
					$input['type'] == "uschoose" ? $student = true : $student = false;
					
                    $upload = $this->fileUpload();
					
					

                    $success = $upload['success'];
                    $written = $success? "true" : "false";

                    ChromePhp::info($student?"Student":"Teacher" . " upload: $written");

                    if($success)
                    {
                        $_SESSION['file'] = $upload['location'];
                    }

                    if(isset($input['console']))
                    {
                        $error = (isset($upload['error']) ? $upload['error'] : "");

                        die("<script type='text/javascript'>window.top.window.uploadComplete($written, '$error');</script>");
                    }

                    if ($success) {
						echo "<script> alert($student);   </script>  " ; 
                        $this->title = "Datei upload zur Aktualisierung der " . $student ? "Schülerdaten" : "Lehrerdaten";
                        $this->prepareDataUpdate($student);
                        $this->actionType = $student ? "usstart" : "utstart";
						$student ? $this->actionType = "usstart" : $this->actionType = "utstart";
						echo $this->actionType; 
                        $this->display("update1");
                    } else {
                        $this->display("update");
                    }

                    break;
                case "dispsupdate1":
                case "disptupdate1":

                    $student = $input['type'] == "dispsupdate1";
                    $this->title = "Datei upload zur Aktualisierung der " . $student ? "Schülerdaten" : "Lehrerdaten";
                    $this->prepareDataUpdate(true);
                    $this->actionType = $student ? "usstart" : "utstart";
                    $this->display("update1");
                    break;
                //Student/Teacher Update start
                case "usstart":
                case "utstart":
					$input['type'] == "usstart" ? $student = true : $student = false;
                    //$student = $input['type'] == "usstart";
echo "Schüleroperation:".$student; die;
                    $this->title = $student?"Schüler":"Lehrer" . "daten aktualisiert";

                    $this->performDataUpdate($student, $input);
                    $this->display("update2");
                    break;
                //SEST configuration
				case "sestconfig":
					$this->title="Konfiguration Elternsprechtag";
					$this->simpleMenueItems[0]=array("link"=>"index.php?type=setclasses","entry"=>"Unterrichtszuordnung einrichten");
					$this->simpleMenueItems[1]=array("link"=>"index.php?type=setslots","entry"=>"Sprechzeiten einrichten");
					$this->backButton="index.php?type=settings";
					$this->display("simple_menue");
					break;
                //News configuration
				case "newsconfig":
					$this->title = "Konfiguration Newsletter (z.B. Emailversand/Anhänge etc.)";
					$this->backButton = "index.php?type=settings";
					$this->display("simple_menue");
					break;
                //Set SEST classes/teachers
				case "setclasses":
					$this->allTeachers = $this->model->getTeachers();
					$this->allForms = $this->model->getForms();
					(isset($input['teacher'] ) ) ? $t = $input['teacher'] : $t = null;
					(isset($input['update'] ) ) ? $u = $input['update'] : $u = null;
					(isset($input['form']) ) ? $f = $input['form'] : $f=null;
					$this->classOperations($f,$u,$t);
					$this->title= "Lehrer zu Klassen zuweisen";
					$this->backButton = "index.php?type=sestconfig";
					$this->display("unterricht");
					break;
				//Set SEST Slots 
				case "setslots":
					$this->title= "Sprechzeiten einrichten";
					$this->backButton = "index.php?type=sestconfig";
					$this->display("simple_menue");
					break;
				//Set SEST Slots
                case "slots":

                    break;
                default:
                    $this->title = "Startseite";
                    unset($_SESSION['file']);
					$this->display("main");
                    break;
            }
    }

    private function handleLogin($input)
    {

        if (!isset($input['login']['mail']) || !isset($input['login']['password'])) {
            ChromePhp::info("No username || pwd in input[]");
            $this->notify('Kein Benutzername oder Passwort angegeben');
            return "adminlogin";
        }

        $pwd = $input['login']['password'];
        $usr = $input['login']['mail'];

        $state = $this->login($usr, $pwd);

        ChromePhp::info("Login Success: $state");

        if (isset($input['console'])) { // used to only get raw login state -> can be used in js
            die(strval($state));
        }

        if ($state == 1) {

            $this->header = "Startseite";
            return "main";
        } else if ($state == 2) {
            ChromePhp::info("No Admin Permission");
            $this->notify('Ungenügende Berechtigung!');
        } else {
            $this->notify("Falsche Benutzername Passwort Kombination!");
        }
        return "adminlogin";
    }

    /**
     *Creates view and sends relevant data
     * @param $template string
     */
    function display($template)
    {
        $view = new View();
		
		$dataForViewKeys=array("title","action","menueItems","backButton","allteachers","allForms","teachersOfForm","currentForm","fileName","fileData");
		$dataForViewValues=array($this->title,$this->actionType,$this->simpleMenueItems,$this->backButton,$this->allTeachers,$this->allForms,
		$this->teachersOfForm,$this->currentForm,$this->file,$this->fileData);
		$view->setDataForView(array_combine($dataForViewKeys,$dataForViewValues)) ;
       
        ChromePhp::info("Displaying 'templates/$template.php' with data: " . json_encode($this->dataForView));
        $view->loadTemplate($template);
    }


    /**
     * Displayes a materialized toast with specified message
     * @param string $message the message to display
     * @param int $time time to display
     */
    function notify($message, $time = 4000)
    {
        if (!isset($this->dataForView))
            $this->dataForView = array();
        if (!isset($this->dataForView['notifications']))
            $this->dataForView['notifications'] = array();

        $notsArray = $this->dataForView['notifications'];

        array_push($notsArray, array("msg" => $message, "time" => $time));

        $this->dataForView['notifications'] = $notsArray;

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

        header("Location: /administrator"); //TODO: this is hardcoded ;-;
    }

    /**
     *check login
     * @param string $mail
     * @param string $pwd
     * @return int 1 => success, 2 => no permission, 0 => invalid login data
     */
    function login($mail, $pwd)
    {
        $model = Model::getInstance();
        if ($model->passwordValidate($mail, $pwd)) {

            $uid = $model->userGetIdByMail($mail);
            if ($uid == null) {
                $this->notify("Database error!");
                $this->display("adminlogin");

                ChromePhp::error("Unexpected database response! requested uid = null!");
                exit();
            }
            $type = $model->userGetType($uid);

            //admin login MUST be type 0
            if ($type == 0) {

                $_SESSION['user']['id'] = $uid;
                $time = $_SESSION['user']['logintime'] = time();
                $_SESSION['user']['pwd'] = $pwd;
                $_SESSION['user']['mail'] = $mail;

                ChromePhp::info("User '$mail' with id $uid of type $type successfully logged in @ $time");
                unset($_SESSION['logout']);
                return 1;
            } else {
                return 2;
            }
        }
        return 0;
    }

    /**
     *uploading a file to server
     * @return array[]
     */
    private function fileUpload()
    {

	   $ret = array("success" => false);
        $success = false;
        try {
            /*
            if(is_uploaded_file($_FILES['Datei']['tmp_name']) &&
            move_uploaded_file($_FILES['Datei']['tmp_name'], '/var/www/vhosts/suso.schulen.konstanz.de/httpdocs/_SusoIntern/uploadtemp/'.$_FILES['Datei']['name'])    )
            {
              $this->file='/var/www/vhosts/suso.schulen.konstanz.de/httpdocs/_SusoIntern/uploadtemp/'.$_FILES['Datei']['name'];
            }*/

            $file = $_FILES['file'];

            if (isset($file['tmp_name']) && is_uploaded_file($file['tmp_name']) &&
                move_uploaded_file($file['tmp_name'], './tmp/' . $file['name'])
            ) {
                $this->file = './tmp/' . $file['name'];
                $ret['success'] = true;
                $ret['location'] = './tmp/' . $file['name'];
            }
        } catch (\Exception $e) {
            $ret['error'] = $e->getMessage();
        } finally {
            return $ret;
        }
    }

    /**
     *prepare update of DB Data
     * @param bool
     */
    private function prepareDataUpdate($student)
    {

        if(!isset($_SESSION['file']))
        {
            header("Location: /administrator"); //TODO: hardcoded ;-;
        }
        $fileHandler = new FileHandler($_SESSION['file']);
        $this->fileData[0] = $fileHandler->readHead();
        $this->fileData[1] = $fileHandler->readDBFields($student); //schueler=true
    }


    /**
     *perform update of DB Data
     * @param bool
     * @param array $input (GET/POST Data)
     */
    private function performDataUpdate($student, $input)
    {
		if(!isset($_SESSION['file']))
        {
            header("Location: /administrator"); //TODO: hardcoded ;-;
        }

        $updateData = array();
        $fileHandler = new FileHandler($_SESSION['file']);
        $sourceHeads = $fileHandler->readHead();
        $x = 0;
        foreach ($sourceHeads as $h) {
            $updateData[] = array("source" => $h, "target" => $input['post_dbfield'][$x]);
            $x++;
        }
        $updateResults = $fileHandler->updateData($student, $updateData);    //gibt Anzahl eingefügter Zeilen an
        $this->fileData[0] = $updateResults[0];
        $this->fileData[1] = $updateResults[1];
        $this->fileData[2] = $fileHandler->deleteDataFromDB($student);
    }
	
	
		/**
		*set teacher class connections
		* @param string form
		* @param array(int) teacherIds
		*/
		private function classOperations($form,$update,$teacher){
		if(isset($update)) {
		$this->model->setTeacherToForm($teacher,$update);
		$form = $update;
		}		
		//read teachers in forms
		if( isset($form) ) {
			$this->currentForm = $form;
			$this->teachersOfForm = $this->model->getTeachersOfForm($form); 
			}
			
		}
}

?>
