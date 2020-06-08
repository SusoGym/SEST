<?php namespace administrator;

/**
 *Controller class handles input and other data
 */
class Controller extends \Controller {
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
    function __construct($input) {
        if (!isset($input['type'])) {
            $input['type'] = "default";
        }
		$this->model = Model::getInstance();
		parent::__construct($input);
        $this->handleLogic(); //the administrator Controllers logic
        
    }
    
    
    // --- Start overriding \Controller ---
    
    /**
     * Handles logic
     */
    protected function handleLogic() {
        $input = $this->input;
		//=====================================================
		//check for timeouts and logout
		//-----------------------------------------------------
		//handle all javascript actions firsthand
		if (isset($input['console'])  ) {
			// timed out in javascript based operations
			if (!isset($_SESSION['user'])){ 
				//no user
				$_SESSION['timeout'] = true;
				$this->logout(true,true); //should now be showing a toast with timeout 
			} else {
				if (!$this->checkLoginTimeout()) {
					die(json_encode(array("time"=>"out","message"=>"Anmeldung abgelaufen!") )) ;
					$_SESSION['timeout'] = true;
					//die($this->logout(true,true)); //should now be showing a toast with timeout 
					//needs js return and action there!
					}
			}
		}
        //-------------end of javascript handling------------
		$loggedIn = false;
		if (isset($_SESSION['timeout']))
			die($this->logout(true));
		if (isset($_SESSION['logout']))
			die($this->logout());
        if (!isset($_SESSION['user']))
			die($this->logout());
		//=======end of timeout and logout check================
		
        switch ($input['type']) {
            case 'logout':
                $_SESSION['logout'] = true;
				die($this->logout());
                break;
            case 'login':
                if (!$loggedIn) {
                    header('Location: ../?type=logout'); //go back to main Login
					//$this->login();
                    break;
                }
            default:
				$this->handleInput();
                /*
				if ($loggedIn) { // a.k.a logged in
                    $this->handleInput();
                } else {
                    $this->display("adminlogin");
                }*/
                break;
        }
    }
    

    /**
     *Creates view and sends relevant data
     *
     * @param $template string
     */
    function display($template) {
        $view = \View::getInstance();
        $data = array();
        
        if (isset($_SESSION['dataForView']['notifications'])) {
            foreach ($_SESSION['dataForView']['notifications'] as $not) {
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
        
        foreach ($myDataForView as $key => $value) {
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
    protected function logout($timeout = null,$console = null) {
        if (isset($_SESSION['timeout']) && $_SESSION['timeout'] == true ) {
			$sessionKey = "timeout";
		} elseif (isset($_SESSION['logout']) && $_SESSION['logout'] == true ) { 
			$sessionKey = "logout"; 
		}
		if (isset($console) ) {
			die(json_encode(array("time"=>"out","message"=>"Anmeldung nach ".$this->sessionValidity." Minuten Inaktivität abgelaufen!\r\n Weiterleitung zur Login Seite erfolgt!") )) ;
		} else {
			session_destroy();
			session_start();
			$_SESSION[$sessionKey] = true;
			die(header("location: ../"));
		}
    }
    
    // --- End overriding \Controller ---
    
    
    /**
     *handles input data
     *
     * @param array $input
     */
    private function handleInput() {
        $input = $this->input;
        //Handle input
        switch ($input['type']) {
            //User Management
            case "usrmgt":
                if (isset($input['console']) && isset($input['partname'])) {
                    $arr = $this->model->getUsers($input['partname']);
                    die(json_encode($arr));
                }
				if(isset( $input['unused']) )  {
					if (isset($input['del']) ) {
					$delete = $input['del'];
					$deletedParent = Model::getInstance()->deleteParentUser($delete);
					$_SESSION['dataForView']['notifications'][] = array("msg" => "Benutzer ".$deletedParent['vorname']." ".
					$deletedParent['name']." wurde gelöscht!", "time" => 4000);
				    }
					$this->infoToView['unused'] = $this->model->getUsersWithoutKids();
					/*header('Content-Type: application/json');
					echo json_encode($unused,JSON_PRETTY_PRINT);
					die;*/
					
					$this->display("unused");					
					} else if (isset($input['search']) )  {
						$this->title = "Eltern suchen (Email)";
						$this->display("usermgt");
					} else {
					$this->title = "Verwaltung";
					$this->addMenueItem("?type=usrmgt&unused=true", "Benutzer ohne Schüler");
					$this->addMenueItem("?type=usrmgt&search=true", "Eltern suchen");
					$this->addMenueItem("?type=pupilmgt", "Schüler suchen");
					$this->addMenueItem("?type=handleregister", "Registrierungsanfragen");
                    $this->addMenueItem("?type=leaveofabsence", "Beurlaubungen");
                    $this->addMenueItem("?type=deregister", "Schülerabmeldung");
					$this->addMenueItem("?type=lockers", "Schließfächer");
					$this->display("simple_menue");
					}
                			
                
                break;
			case "pupilmgt":
			if (isset($input['console']) && isset($input['partname'])) {
					$absenceManagement = (isset($input['absence']) && $input['absence'] === "true") ? true : false ;
                    $arr = $this->model->getPupils($input['partname'],$absenceManagement);
					die(json_encode($arr));
                }
            
            //Funktion zum Löschen eines Schülers
            if (isset($input['console']) && isset($input['dereg']) ) {
                $this->model->deregisterStudent($input['dereg']);
                $arr = array("status" => "deleted","message" => "Schüler*in gelöscht");
                die(json_encode($arr) );
                }
			$this->backButton = "?type=usrmgt";
			$this->title = "Schüler suchen";
			$this->display('deregister'); //"pupilmgt"
			break;
			case "handleregister":
			if(isset($input['console'])) {
				//handle actions
				if (isset($input['delete'])) {
					$this->model->deleteRegistrationRequest($input['id']);
					die(json_encode(array("status"=>"request_deleted","message"=>"Anfrage gelöscht!","id"=>$input['id']) ) );
					}
				if (isset($input['confirm'])) {
					if ($mailData = $this->model->finishRegistrationRequest($input['request'] , $input['pupil']) ) {
						// \Debug::writeDebugLog(__method__,"Anfrage Nr: ".$input['request']." von Email: ".$mailData['email']);
						$studentToRegister = $this->model->getStudentById($input['pupil']);
						//send mail with mailData array
						$body = mb_convert_encoding("Sie haben am ".$mailData['date']." einen Registrierungscode für ".
						$studentToRegister->getFullName()." (".$studentToRegister->getClass().") angefordert. 
						<br>Registrieren Sie Ihr Kind mit dem Code ". $studentToRegister->getASVId().
						'<br><br>Diese Nachricht wurde automatisch generiert!','UTF-8'); 
						\Debug::writeDebugLog(__method__,"Mail an: ".$mailData['email']." mit Nachricht\n".$body);
						if ($this->sendKeyRequestMail($mailData['email'],$body) ) {
						//sending successful
						$status = "email_sent"; $message = "Registrierungscode gesendet!";
						} else {
						//error sending
						$status = "error"; $message = "Senden fehlgeschlagen!";
						}
						die(json_encode(array("status"=>$status,"message"=>$message,"id"=>$input['request']) ));
					} else {
						die(json_encode(array("status"=>"error","message"=>"something went wrong!")));
					}
					}
				}
			$this->backButton = "?type=usrmgt";
			$this->title = "Registrierungsanfragen bearbeiten";
			$this->infoToView['requests'] = Model::getInstance()->getRegistrationRequests();
			$this->display('handleregistration');
			
			break;
			case "leaveofabsence":
			
			//enter  a student absence as leave of absence
			if(isset($input['console'])) {
					$this->model->enterAbsentPupil($input['id'],$input['start'],$input['end'],$input['comment'],$_SESSION['user']['id'],0,1,1); 
					$arr = array("status" => "loaEntered",
					"id" => $input['id'],
					"studentList" => $this->model->getLeaveOfAbsenceStudents() ); 
					die(json_encode($arr)); //DOES NOT COME ACROSS WHY????
				}
			$this->infoToView['studentList'] = $this->model->getLeaveOfAbsenceStudents(); 	
			$this->display('leaveofabsence');
			break;
            case "usredit":
                $usr = $input['name'];
                $usr = $this->model->getUserByMail($usr);
                if ($usr == null) {
                    $this->notify("Error: Invalid user to be edited!");
                    $this->title = "Eltern suchen (Email)";
                    $this->display("usermgt");
                    return;
                }
                if (isset($input['edit'])) {
                    $mail = $input['f_email'];
                    $surname = $input['f_surname'];
                    $name = $input['f_name'];
                    
                    $pwd = isset($input['f_pwd']) ? $input['f_pwd'] : null;
                    $pwd_rep = isset($input['f_pwd_repeat']) ? $input['f_pwd_repeat'] : null;
                    
                    if ($pwd != $pwd_rep) {
                        $_SESSION['dataForView']['notifications'][] = array("msg" => "Die eingegbenen Passwörter stimmen nicht überein!", "time" => 4000);
                    } else {
                        if ($pwd != "" && $pwd_rep != "") {
                            $this->model->changePwd($usr->getId(), $pwd);
                        }
                        if ($usr->getEmail() != $mail || $usr->getName() != $name || $usr->getSurname() != $surname) {
                            $this->model->updateUserData($usr->getId(), $name, $surname, $mail);
                        }
                        $_SESSION['dataForView']['notifications'][] = array("msg" => "Die Nuterdaten wurden erfolgreich geändert!", "time" => 4000);
                    }
                    
                    header("Location: ?type=usredit&name=$mail");
                    die();
                }
                $this->title = "Edit: " . $usr->getEmail();
                $this->backButton = "?type=usrmgt";
                $this->infoToView['user'] = $usr;
				$this->infoToView['kids'] = $usr->getChildren();
                $this->display("usredit");
                break;
            //deregister student
            case "deregister":
			    if(isset($input['console'])) {
                    if (isset($input['partname'])) {
                        $arr = $this->model->getPupils($input['partname'],true);
                        die(json_encode($arr));
                        }
                    if (isset($input['getdata'])) {
                        $pupilData = $this->model->getStudentDataJSON($input['getdata']);
                        $arr = array(); 
                        $arr = array("status" => "chosen","studentdata" => $pupilData);
                        }
                    

                    //get all the relevant data 
                    //get parents 

                    //get library books which need to be handed back
                        
                    die(json_encode($arr)); 
                    }
                $this->title = "Schülerabmeldung";
                $this->display('deregister');
                break;
			//Settings
            case "settings":
                $this->title = "Einstellungen";
                $this->addMenueItem("?type=sestconfig", "Elternsprechtag");
                $this->addMenueItem("?type=newsconfig", "Newsletter");
                $this->addMenueItem("?type=options", "Optionen");
				$this->addMenueItem("?type=admpwd", "Passwort ändern");
                $this->display("simple_menue");
                break;
            //call Newsletter function
            case "news":
                $this->title = "Newslettermanagement";
				$this->addMenueItem("?type=archive", "Archiv");
                $this->addMenueItem("?type=enternews", "neuer Newsletter");
                $this->display("simple_menue");
                break;
			//view existing news
			case "archive":
				$this->title = "Übersicht";
				$this->getNewsletters();
				$this->display("newsarchive");
				break;
			//enter news
			case "enternews":
			    if (isset($input['nl'])) {
					$this->infoToView["newsid"] = $input['nl'];
					$this->title = "Newsletter bearbeiten";
					$newsletter = new \Newsletter();
					$newsletter->createFromId($input['nl']);
					$this->infoToView['editingnewsletter'] = $newsletter;
					}
				else {
					$this->infoToView["newsid"] = null;
					$this->title = "Newsletter erstellen";
					}
				$this->infoToView["button"] = "Speichern";
				$this->infoToView["link"] = "savenews";
				$this->display("enternews");
				break;
			//save News
			case "savenews":
				$this->title = "Newsletter gespeichert";
				$newsletter = new \Newsletter();
				$newsletter->createFromPOST($_POST['nldate'], $_POST['nltext'], isset($_POST['nl']) ? $_POST['nl'] : null );
				$this->getNewsletters();
				$this->display("newsarchive");
				break;
			//view news
			case "view":
				$this->title = "Newsletter lesen";
				$newsletter = new \Newsletter();
				$newsletter->createFromId($input['nl']);
				$this->infoToView["newsletter"] = $newsletter;
				$this->infoToView["user"] = self::$user;
				$this->display("viewnews");
				break;
			//send News
			case "sendnews":
				$this->title = "Newsletter versendet";
				$newsletter = null;
				$list = null;
				if (isset($input['nl'])) {
					$newsletter = new \Newsletter();
					$newsletter->createFromId($input['nl']);
					}
				//Ermittle Empfänger
			    $list = $this->model->getNewsRecipients();
				$this->sendNewsletterMails($list,$newsletter);
				$this->getNewsletters();
				$this->display("newsarchive");
				break;
            //Select update options
            case "updmgt":
                $this->title = "Datenabgleich";
                $this->addMenueItem("?type=update_s", "Schülerdaten");
                $this->addMenueItem("?type=update_t", "Lehrerdaten");
				$this->addMenueItem("?type=upload_u", "Unterrichtsdaten");
                $this->addMenueItem("?type=upload_e", "Terminedatei");
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
           //lessons file upload
            case "upload_u":
                $this->title = "Upload Unterrichtsdaten";
                $this->actionType = "lessonchoose";
                $this->display("update");
                break;
            
            //file upload
            case "utchoose":
            case "uschoose":
            case "eventchoose":
			case "lessonchoose":
                $student = $input['type'] == "uschoose";
                //von mir hinzugefügt
                $input['type'] == "uschoose" ? $student = true : $student = false;
                
                $upload = $this->fileUpload();
                $success = $upload['success'];
                $written = $success ? "true" : "false";
                \ChromePhp::info($student ? "Student" : "Teacher" . " upload: $written");
                
                if ($success) {
                    echo $_SESSION['file'] = $upload['location'];
                }
                
                if (isset($input['console'])) {
                    $error = (isset($upload['error']) ? $upload['error'] : "");
                    
                    die("<script type='text/javascript'>window.top.window.uploadComplete($written, '$error');</script>");
                }
                
                if ($success) {
                    
                    echo "<script> alert($student);   </script>  ";
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
            
            // Update Events
            case "dispupdateevents":
                $this->manageEvents();
                $this->title = "Termine";
                $this->infoToView['cardtext'] = "Termine aktualisiert und ics-Dateien erzeugt";
                $this->display("events");
                break;
			// Update classes taught by teacher
            case "dispupdatelessons":
				$error = $this->manageLessons();
                if (isset($error) ) {
					$this->notify("Fehler - falsches Lehrerkürzel ".$error." gefunden!");
				} else {
					$this->notify("Unterrichtsdaten erfolgreich aktualisiert!");
				}
                $this->title = "Unterrichtszuordnung";
                $this->infoToView['cardtext'] = "Unterrichtszuordnung aktualisiert";
                $this->display("lessons");
                break;
            //SEST configuration
            case "sestconfig":
            case "clrslts":
            case "chkass":
            case "showtchrappmt":
                $this->title = "Konfiguration Elternsprechtag";
                $this->addMenueItem("?type=setclasses", "Unterrichtszuordnung einrichten");
                if (date('Ymd') <= $this->model->getOptions()['assignstart']) {
                    $this->addMenueItem("?type=setslots", "Sprechzeiten einrichten");
                    $this->addMenueItem("?type=clrslts", "buchbare Termine zurücksetzen");
                }
                $this->addMenueItem("?type=chkass", "Lehrertermine prüfen");
                $this->addMenueItem("?type=showtchrappmt", "Alle Lehrertermine anzeigen");
                if ($this->input['type'] == "clrslts") $this->clearSlots();
                if ($this->input['type'] == "chkass") $this->checkTeacherAssignments();
                if ($this->input['type'] == "showtchrappmt") $this->showTeacherAppointments();
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
                if (isset($input['sbm'])) {
                    
                    $this->model->updateOptions($_POST);
                }
                $this->title = "Konfiguration Optionen";
                $this->backButton = "?type=settings";
                $this->infoToView['options'] = $this->model->getOptionsForAdmin();
                $this->display("options");
                break;
            // change admin user Password
			case "admpwd":
				$this->backButton = "?type=settings";
				if (isset($input['console'])) {
                    $data = $input['data'];
					$pwd = $data['pwd'];
					$oldpwd = $data['oldpwd'];
					//change Password
					if ($oldpwd == "") {
						die(json_encode(array("success" => false, "notifications" => array("Bitte geben sie ihr altes Passwort an!"))));
						} else if (!$this->model->passwordValidate(self::getUser()->getEmail(), $oldpwd)) {
							die(json_encode(array("success" => false, "notifications" => array("Ihr altes Passwort ist nicht korrekt!"), "resetold" => true)));
						}
					if ($pwd != "") {
						$this->model->changePwd(self::$user->getId(), $pwd);
						$_SESSION['user']['pwd'] = $pwd;
						die(json_encode(array("success" => true, "notifications" => array("Passwort geändert!"))));
					}
                }
				$this->infoToView['user'] = self::$user;
				$this->display("admincreds");
				die;
			//Set SEST classes/teachers manually
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
                if (isset($input['del'])) {
                    $this->model->deleteSlot($input['del']);
                    //this->model->deleteBookableSlots($input['del']) - does not exist yet in model
                }
                if (isset($input['start'])) {
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
			case "checkprevabs":
				//check if absence one day before startdate exists
				if(isset($input['console'])) {
				$previousDayAbsence = $this->model->getPreviousDayAbsence($input['id'],$input['date']);	
				$arr = array("status" => "previousDayAbsence","aid" => $previousDayAbsence); 
					die(json_encode($arr));
				}
				break;
			case "addtoabsence":
				if(isset($input['console'])) {
				$this->model->addToAbsence($input['aid'],$input['end'],$_SESSION['user']['id'],$input['via']);
				$arr = array("status" => "absenceProlonged",
				"studentList" => json_encode(array_merge($this->model->getAbsentStudents(),$this->model->getMissingExcuses()))); 
				
				die(json_encode($arr));
				}
				break;
			case "markabsent":
				if(isset($input['console'])) {
					$this->model->enterAbsentPupil($input['id'],$input['start'],$input['end'],$input['comment'],$_SESSION['user']['id'],$input['via']); 
					$arr = array("status" => "absenceEntered","id" => $input['id'],
					"studentList" => json_encode(array_merge($this->model->getAbsentStudents(),$this->model->getMissingExcuses()))); 
					die(json_encode($arr));
				}
				else {
					die("What are you doing here!");
				}
				break;
			case "editabsence":
				if(isset($input['console'])) {
				$editedDataSet = $this->model->editAbsence($input['aid'],$input['start'],$input['end'],$input['ecomment'],$input['evia'],$_SESSION['user']['id']);
				if ($input['loa'] == "true") {
					$arr = array("status" => "loaEdited",
					"aid" => $input['aid'],
					"studentList" => $this->model->getLeaveOfAbsenceStudents());
				} else {
				
					$arr = array("status" => "absenceEdited",
					"aid" => $input['aid'],
					"studentList" => json_encode(array_merge($this->model->getAbsentStudents(),$this->model->getMissingExcuses())) );
						/*"id" => $input['id'],
						"missingStudents" => $this->model->getAbsentStudents(),
						"missingExcuses" => $this->model->getMissingExcuses());*/
				}
				die(json_encode($arr));
				}
				break;
			case "deleteabsence":
				if(isset($input['console'])) {
						$this->model->deleteAbsence($input['aid']);
						$arr = array("status" => "absenceDeleted","aid" => $input['aid']); 
						die(json_encode($arr));
				}
				break;
			case "excuse":
				if(isset($input['console'])) {
				$this->model->enterExcuse($input['aid'],$input['date'],$input['comment']);
				$arr = array("status" => "absenceExcused",
				"aid" => $input['aid'],
				"excused" => $input['date'],
				"studentList" => json_encode(array_merge($this->model->getAbsentStudents(),$this->model->getMissingExcuses())) );
				die(json_encode($arr));
				}
				
				break;
			case "printabsence":
				//create a text file of absentees as basis for print
				$this->makeAbsenteesFile();
				$arr = array("status" => "pdfready");
				die(json_encode($arr));
				break;
			case "lockers":
				//locker management
				
				if (isset($input['console']) && isset($input['hire']) ) {
					$this->model->hireoutLocker($input['lckr'],$input['stdnt']);
					$arr = array("status" => "hired", "message" => "Schließfach erfolgreich vergeben");
					die(json_encode($arr) );
					} else if (isset($input['return']) ){
						$this->model->returnLocker($input['lckr']);
						$arr = array("status" => "returned", "message" => "Schließfach erfolgreich zurückgegeben");
						die(json_encode($arr) );
						}
				//prepare data to be sent to templates
				//lockerlist & studentList
				//get Lockers with students as JSON
				$lockers = $this->model->getLockerList();
				
				$this->infoToView['lockers'] = json_encode($lockers);

				$this->display("lockermgt");
				break;
            default:
				//Landing Page 
                $this->title = "Startseite";
                unset($_SESSION['file']);
				//get startpage data
				//missing students for the day
				$this->infoToView['missingStudents'] =  json_encode($this->model->getAbsentStudents());
				
				$this->infoToView['missingExcuses'] =  json_encode($this->model->getMissingExcuses());
				$this->infoToView['studentList'] = json_encode(array_merge($this->model->getAbsentStudents(),$this->model->getMissingExcuses()));
				$this->infoToView['isadmin'] = "true";
				//get coverLessons
				// not yet implemented
				$this->infoToView['VP_allDays'] = $this->model->getVPDays(true);
				$this->infoToView['VP_coverLessons'] = $this->model->getAllCoverLessons(true, null, $this->infoToView['VP_allDays']);
				// should include a link to show coverLessons
				//modal or separate display??
				
				
                $this->display("main");
                break;
            
            
            
        }
    }
    
    /**
     * uploading a file to server
     *
     * @return array[]
     */
    private function fileUpload() {
        
        $ret = array("success" => false);
        $success = false;
        try {
            /*
            if(is_uploaded_file($_FILES['file']['tmp_name']) &&
            move_uploaded_file($_FILES['file']['tmp_name'], '/var/www/vhosts/suso.schulen.konstanz.de/httpdocs/_SusoIntern/uploadtemp/'.$_FILES['file']['name'])    )
            {
              $this->file='/var/www/vhosts/suso.schulen.konstanz.de/httpdocs/_SusoIntern/uploadtemp/'.$_FILES['file']['name'];
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
     *
     * @param bool
     */
    private function prepareDataUpdate($student) {
        
        if (!isset($_SESSION['file'])) {
            header("Location: /administrator"); //TODO: hardcoded ;-;
        } else if (!file_exists($_SESSION['file'])) {
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
    private function performDataUpdate($student, $input) {
        if (!isset($_SESSION['file'])) {
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
     * Make Events and write ICS file
     */
    private function manageEvents() {
        $filehandler = new FileHandler($_SESSION['file']);
        $events = $filehandler->readEventSourceFile();
        $tmanager = new TManager();
        $tmanager->addEventsToDB($events);
        //TO DO make ICS Files for staff and others
        $tmanager->createICS($events);
        $tmanager->createICS($events, true); //create StaffVersion
        
    }
	
	/**
	* assign teachers to classes 
	*/
	private function manageLessons() {
	$filehandler = new FileHandler($_SESSION['file']);
	$lessons = $filehandler->readLessonsSourceFile();
	$error = $this->model->setLessons($lessons);
	if (isset($error)) {
		return $error;
		} else {
		return null;	
		}
	}
    
    /**
     * Adds Menu Item
     *
     * @param $link string
     * @param $name string
     * @return void
     */
    private function addMenueItem($link, $name) {
        array_push($this->menueItems, array("link" => $link, "entry" => $name));
    }
    
    /**
     *set teacher class connections
     *
     * @param string $form
     * @param array(int) teacherIds
     */
    private function classOperations($form, $update, $teacher) {
        if (isset($update)) {
            $this->model->setTeacherToForm($teacher, $update);
            $form = $update;
        }
        //read teachers in forms
        if (isset($form)) {
            $this->currentForm = $form;
            $this->teachersOfForm = $this->model->getTeachersOfForm($form);
        }
        
    }
    
    /**
     * clears bookable_slots and sets news
     */
    private function clearSlots() {
        $this->model->clearBookableSlots();
        foreach ($this->model->getSlots() as $slot) {
            $this->model->createBookableSlots($slot['id']);
        }
        $this->notify("Buchbare Termine zurückgesetzt und aktualisiert");
    }
    
    /**
     * checks assigned slots of Teachers
     */
    private function checkTeacherAssignments() {
        $params = $this->model->getIniParams();
        $fileName = "teacherassignments.html";
        $path = $params['filebase'] . '/' . $params['download'] . '/' . $fileName;
		$relPath = '../' . $params['download'] . '/' . $fileName;
        $teachers = $this->model->getTeachers();
        $data = array();
        $line = "Lehrer;Deputat;Anzahl zu vergebender Termine;Anzahl noch zu vergebender Termine;Vergebene Termine\n";
		$line = "<html>\r\n
				<head><meta charset=\"utf-8\"><title>Elternsprechtag-Slots</title></head>\r\n
				<table border='1' bordercolor='#000000'>\r\n
				<tbody>\r\n
				<tr style=\"font-weight: bold;\">\r\n
					<td>Lehrer</td>\r\n
					<td>Deutat</td>\r\n
					<td>Anzahl Pflicht:</td>\r\n
					<td>Anzahl offen</td>\r\n
					<td>Bereits vergeben</td>\r\n
				</tr>\r\n";
		
        array_push($data, $line);
        foreach ($teachers as $teacher) {
            $asString = null;
            $deputat = $teacher->getLessonAmount();
            $requiredSlots = $teacher->getRequiredSlots();
            $missingSlots = $teacher->getMissingSlots();
            $assignedSlots = $teacher->getAssignedSlots(); //array()
            $x = 0;
            foreach ($assignedSlots as $as) {
                $asString = $x == 0 ? $as : $asString . "|" . $as;
                $x++;
            }
			$color = ($missingSlots > 0)  ? "#ff0000" : "#21610B";
				
            $line = "<tr style=\"color: $color;\">\r\n
						<td >".mb_convert_encoding($teacher->getFullName(),'UTF-8')."</td>\r\n
						<td>".$deputat."</td>\r\n
						<td>".$requiredSlots."</td>\r\n
						<td>".$missingSlots ."</td>\r\n
						<td>".$asString ."</td>\r\n
					</tr>\r\n";
            array_push($data, $line);
            
        }
        $filehandler = new Filehandler($path);
		array_push($data,"</tbody></table></body></html>");
        $filehandler->createCSV($data);
        $this->notify("Datei " . $fileName . " erzeugt");
		?><script type="text/javascript" language="Javascript">window.open('<?php echo $relPath; ?>');</script><?php
    }

    /**
     * show all teaxcher appointments at Elternsprechtag
     * and enable cancelling
     * 
     */
    private function showTeacherappointments() {
        $this->title = "Elternsprechtagstermine";
        $this->notify("function not available yet");
        $this->infoToView['teachers'] = $this->model->getTeachers();
        $this->display("showappointments");
    }
	
	/**
	* create absentees basis file to print out
	*/
	private function makeAbsenteesFile(){
	$maxPeriod = 3; // workdays
	$absentees = $this->model->getAbsenteeListData($maxPeriod);
	$filehandler = new Filehandler("./templates/absentees.txt");
	$filehandler->createHTML($absentees,$maxPeriod);
	}
		
	/**
     *
     * triggering email via phpmailer
     * @param array() containing list of mail recipients (User object)
	 * @param NewsletterObject
     */
    private function sendNewsletterMails($list,$newsletter) {
        $currentTime = date('d.m.Y H:i:s');
        //$this->model->writeToVpLog("Starting to send mails on " . $currentTime);
        require("../PHPMailer.php");
        //sending emails
        $timestamp = time();
        $datum = date("Y-m-d  H:i:s", $timestamp);
		/** @var User $$userObj */
        foreach ($list as $userObj) {
            /** @var PHPMailer $phpmail */
            $phpmail = new \PHPMailer();
            $phpmail->setFrom("newsletter@suso.konstanz.de", "Suso-Gymnasium Newsletter");
            $phpmail->CharSet = "UTF-8";
            if ($userObj->getNewsStatus()) {$phpmail->isHTML();}
            $phpmail->AddAddress($userObj->getEmail());
            $phpmail->Subject = 'Suso-Newsletter vom '.$newsletter->getNewsDate();
            $phpmail->Body = ($userObj->getNewsStatus() ) ? $newsletter->makeViewText($userObj,true,true) : $newsletter->makeViewText($userObj,false);
            
			//Senden
            if (!$phpmail->Send()) {
                echo "cannot send!";
                //$mail[$x]->Send() liefert FALSE zurück: Es ist ein Fehler aufgetreten
                $currentTime = date('d.m.Y H:i:s');
                //$this->model->writeToVpLog("....failure." . $phpmail->ErrorInfo . " Trying to reach " . $l->getEmail() . " " . $currentTime);
            } else {
                //echo "mail gesendet an: " . $userObj->getEmail() . '<br>';
                //Eintrag des Sendeprotokolls
                $currentTime = date('d.m.Y H:i:s');
                //$this->model->writeToVpLog($userObj->getEmail() . " " . $currentTime);
                
                //Inhalt
                //$this->model->writeToVpLog("....success");
            }
                    
        unset($text);               
           
        }
        //Enter Send Date
		$this->model->enterNewsSentDate($newsletter->getId());
    }
	
	
	
	    
}

?>
