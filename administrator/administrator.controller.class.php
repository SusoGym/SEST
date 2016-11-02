<?php namespace administrator;

/**
 *Controller class handles input and other data
 */
class Controller
{
    /**
     * @var string file name
     */
    private $file;

    /**
     * @var array data - various data to be used in view
     */
    private $dataForView = array();

    /**
     * @var string header for view
     */
    private $header;

    /**
     * @var string actiontype For view
     */
    private $actionType;

    /**
     * @var Model
     */
    private $model;

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
                    $this->header = "Benutzerverwaltung";
                    $this->display("usermgt");
                    break;
                //Settings
                case "settings":
                    $this->header = "Einstellungen";
                    $this->display("settings");
                    break;
                //Enter Newsletter
                case "news":
                    $this->header = "Newsletter eintragen";
                    $this->display("enternews");
                    break;
                //Select update options
                case "updmgt":
                    $this->header = "Datenabgleich";
                    $this->display("updatemenue");
                    break;
                //Update teacher data
                case "update_t":
                    //Einlesen der Lehrerdaten
                    $this->header = "Lehrerdaten abgleichen";
                    $this->actionType = "utchoose";
                    $this->display("update");
                    break;
                //Update student data
                case "update_s":
                    $this->header = "Schülerdaten abgleichen";
                    $this->actionType = "uschoose";
                    $this->display("update");
                    break;
                //student file upload
                case "utchoose":
                case "uschoose":

                    $student = $input['type'] == "uschoose";

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
                        $this->header = "Datei upload zur Aktualisierung der " . $student ? "Schülerdaten" : "Lehrerdaten";
                        $this->prepareDataUpdate(true);
                        $this->actionType = $student ? "usstart" : "utstart";
                        $this->display("update1");
                    } else {
                        $this->display("update");
                    }

                    break;
                case "dispsupdate1":
                case "disptupdate1":

                    $student = $input['type'] == "dispsupdate1";
                    $this->header = "Datei upload zur Aktualisierung der " . $student ? "Schülerdaten" : "Lehrerdaten";
                    $this->prepareDataUpdate(true);
                    $this->actionType = $student ? "usstart" : "utstart";
                    $this->display("update1");
                    break;
                //Student/Teacher Update start
                case "usstart":
                case "utstart":

                    $student = $input['type'] == "usstart";

                    $this->header = $student?"Schüler":"Lehrer" . "daten aktualisiert";

                    $this->performDataUpdate($student, $input);
                    $this->display("update2");
                    break;
                //SEST configuration
                case "sestconfig":
                    $this->header = "Konfiguration Elternsprechtag (z.B. Klassen/Lehrer, Slotdaten eingeben etc.)";
                    $this->display("settings");
                    break;
                //News configuration
                case "newsconfig":
                    $this->header = "Konfiguration Newsletter (z.B. Emailversand/Anhänge etc.)";
                    $this->display("settings");
                    break;
                //Set SEST Slots
                case "slots":

                    break;
                default:
                    $this->header = "Startseite";
                    $this->display("main");
                    unset($_SESSION['file']);
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

        if (isset($this->dataForView)) {
            $view->setViewData($this->dataForView);
        }
        if (isset($_SESSION['file'])) {
            $view->setFile($_SESSION['file']);
        }
        if (isset($this->header)) {
            $view->setHeader($this->header);
        }
        if (isset($this->actionType)) {
            $view->setActionType($this->actionType);
        }

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
        $this->dataForView[0] = $fileHandler->readHead();
        $this->dataForView[1] = $fileHandler->readDBFields($student); //schueler=true
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
        $this->dataForView[0] = $updateResults[0];
        $this->dataForView[1] = $updateResults[1];
        $this->dataForView[2] = $fileHandler->deleteDataFromDB($student);
    }
}

?>
