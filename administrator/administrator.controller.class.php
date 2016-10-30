<?php

/**
 *Controller class handles input and other data
 */
class Controller
{
	/**
	*@var string template
	*/
	private $tpl="adminlogin";
	
	/**
	*@var string file name
	*/
	private $file;
	
	/**
	*@var array data - various data to be used in view
	*/
	private $dataForView=array();
	
	/**
	*@var string header for view
	*/
	private $header;
	
/**
*Konstruktor
*@param array 
*/	
  function __construct($input)
  {
	
	/*
      ChromePhp::info("-------- Next Page --------");
      ChromePhp::info("Input: " . json_encode($input));
      ChromePhp::info("Session: " . json_encode($_SESSION));
	*/
      $model = Model::getInstance();
      $this->infoToView = array();
	if(isset($_SESSION['user']['name']) && isset($_SESSION['user']['pwd'])){
		$this->handleInput($input);	
		}
	else{
		//check login
		if (isset($input['type'])){
		 //Start login logic
            if($input['type']=="login"){
				$this->tpl = "adminlogin";
				if(!isset($input['login']['user']) || !isset($input['login']['password'])){
					ChromePhp::info("No username || pwd in input[]");
					$this->notify('Kein Benutzername oder Passwort angegeben');
					$this->display();
					break;
					}
				$pwd = $_SESSION['user']['pwd'] = $input['login']['password'];
				$usr = $_SESSION['user']['name'] = $input['login']['user'];
				if(isset($input['console'])){ // used to only get raw login state -> can be used in js
					die($this->login($usr, $pwd) ? "true" : "false");
					}
				if ($this->login($usr, $pwd)) {
					$this->header="Startseite";
					$this->tpl = "main";
					} 
				else {
					ChromePhp::info("Invalid login data");                 // eigentlich sollte man das mit js machen, damit Seite bei (fehlerhaft) anmelden nicht neu läd.....
					$_SESSION['failed_login']['name'] = $usr;
					$this->notify('Benutzername oder Passwort falsch');
					}
				}
		}
		else{
			$this->tpl="adminlogin";
			}		
	}
	$this->display();
}

  
  /**
  *handles input data
  *@param array input
  */
  private function handleInput($input){
	//Handle input
    if (isset($input['type'])) {
      switch ($input['type']) {
 		//User Management
		case "usrmgt":
		    $this->header="Benutzerverwaltung";
			$this->tpl="usermgt";
			break;
		//Settings
		case "settings":
		    $this->header="Einstellungen";
			$this->tpl="settings";
			break;
		//Enter Newsletter
		case "news":
			$this->header="Newsletter eintragen";
			$this->tpl="enternews";
			break;
		//Select update options
		case "updmgt":
			$this->header="Datenabgleich";
			$this->tpl="updatemenue";
			break;
		//Update teacher data
  	    case "update_t":
  	      //Einlesen der Lehrerdaten 
		  $this->header="Lehrerdaten abgleichen";
		  $this->tpl = "update";
          break;
        //Update student data
        case "update_s":
  	      $this->header="Schülerdaten abgleichen";
		  $this->tpl = "update";
  	      break;
		//student file upload
		case "uschoose":
			$this->header="Datei upload";
			/*
			if(is_uploaded_file($_FILES['Datei']['tmp_name']) &&
			move_uploaded_file($_FILES['Datei']['tmp_name'], '/var/www/vhosts/suso.schulen.konstanz.de/httpdocs/_SusoIntern/uploadtemp/'.$_FILES['Datei']['name'])    )
			{
			  $this->file='/var/www/vhosts/suso.schulen.konstanz.de/httpdocs/_SusoIntern/uploadtemp/'.$_FILES['Datei']['name'];
			}*/
			if(is_uploaded_file($_FILES['Datei']['tmp_name']) &&
			move_uploaded_file($_FILES['Datei']['tmp_name'], './tmp/'.$_FILES['Datei']['name'])    )
			{
			  $this->file='./tmp/'.$_FILES['Datei']['name'];
			}
			else
			{
			  echo 'Bei dem Upload ist ein Fehler aufgetreten.';
			  die;
			}
			
			$fileHandler=new FileHandler($this->file);
			$this->dataForView[0]=$fileHandler->readHead();
			$this->dataForView[1]=$fileHandler->readDBFields(true); //schueler=true
			$this->tpl="update1";
			break;
		//Student Update start
		case "usstart":
			$this->header="Daten aktualisieren";
			$updateData=array();
			$this->file=$input['file'];
			$fileHandler=new FileHandler($this->file);
			$sourceHeads=$fileHandler->readHead();
			$x=0;
			foreach($sourceHeads as $h){
				$updateData[]=array("source"=>$h,"target"=>$input['post_dbfield'][$x]);
				$x++;
				}
			$this->dataForView[]=$fileHandler->updateData(true,$updateData);	//gibt Anzahl eingefügter Zeilen an
			$this->dataForView[]=$fileHandler->deleteDataFromDB(true);
			$this->tpl="update2";
			break;
		
		//Set SEST Slots
  	    case "slots":
  	      
  	      break;
       //Logout
  	    case "logout":
  	      session_destroy();
          session_start();
          $_SESSION['logout'] = true; // notify about logout after reloading the page to delete all $_POST data
          header("Location: /");
  	      break;
        //Default
  	    default:
  	      session_destroy();
		  //ChromePhp::error("Error: invalid type in input[] specified");
  	      $this->tpl = "adminlogin";
  	      $this->notify('A fehler occurred');
       }
    } 
	else {
		if(isset($_SESSION['user']['name']) && isset($_SESSION['user']['pwd'])){
            // alread logged in!
            $name = $_SESSION['user']['name'];
            $pwd = $_SESSION['user']['pwd'];
            if($this->login($name, $pwd)){
                //ChromePhp::info("Relogin with valid user data");
                $this->header="Startseite";
				$this->tpl = "main";
                return;
				}
            else{
                ChromePhp::info("Relogin with invalid user data. Redirecting to login page");
            }
			}
        else{
			//zur Login Seite
			$this->tpl="adminlogin";
			}
    }	
  }
  
  
  
  /**
   *Creates view and sends relevant data
   */
  function display()
  {
    $view = new View();
	if(isset($this->viewHeader)) $view->header=$this->viewHeader;
	if(isset($this->dataForView)) {$view->setViewData($this->dataForView);}
	if(isset($this->file)) {$view->setFile($this->file);}
	if(isset($this->header)) {$view->setHeader($this->header);}
	$view->loadTemplate($this->tpl);
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

          //ChromePhp::info("User '$usr' with id $uid of type $type successfully logged in @ $time");

          return true;
      }

      return false;
  }


}

?>
