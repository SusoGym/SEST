<?php

/**
 * class handles input and other data
 */
class Controller {
    
    /**
     * @var Model instance of model to be used in this class
     */
    protected $model;
    /**
     * @var array combined POST & GET data received from client
     */
    protected $input = array();
    
    /**
     * @var User
     */
    protected static $user;
    
    /**
     * @var logfile
     */
    protected $logfile = "vpaction.log";
	
	/**
	* @var sessiontime in Minutes
	*/
	protected $sessionValidity = 20;
    
    /**
     * @return User
     */
    public static function getUser() {
        return self::$user;
    }
    
    /**
     * Controller constructor.
     *
     * @param $input
     */
    public function __construct($input) {
        if ($this->model == null)
            $this->model = Model::getInstance();
        $this->input = $input;
        $this->infoToView = array();
		$this->chooseLogicHandler();
	}
	
	/**
	*debug function
	*/
	protected function readSession(){
	$msg="";
	if(!empty($_SESSION) ){
		$msg = "SESSION:\r\n";
		foreach ($_SESSION as $key => $value){
			if ($key != "notifications") {
			$msg .= $key." -- ";
			if (is_array($value) ) {
				foreach ($value as $k=>$v) {
						$msg .= "[".$k. " : ".$v."]\r\n";
				}
			} else {
				$msg .= $value;
			}
			$msg .="\r\n";
			}
		}
	} else {
		$msg = "No Session set";
	}
	return $msg;
	}
	
	/*
	* check which logicHandler will be used
	* admin login uses handler in admin.controller class
	*/
	protected function chooseLogicHandler(){
	//choose the logic handler for admin and other users
		if (isset($_SESSION['user']) ) {
			   
			   if ($_SESSION['user']['type'] == 0){
					//admin is logged in
					if (!$this->checkLoginTimeout()) {
						die($this->logout(true));//calls admin controllers logout()
															
					}else {
						return;
					}
				} else {
					//all other users
					//Debug::writeDebugLog(__method__,$this->readSession()."\r\nother than Admin logged in");
					if (!$this->checkLoginTimeout()) {
						die($this->logout(true));//calls admin controllers logout()
						} else {
						$this->handleLogic();
						}					
				}
			} else {
			//no user logged in - goto login page
			//System Timeout, i.e. SESSION timeout of server OR NEVER LOGGED IN, i.e. START ???
			//need to show a message
			//Debug::writeDebugLog(__method__,$this->readSession()."\r\nNo one logged in");
			$this->handleLogic();
		}	
	}
    
    /*
	* handle logic
	* start all relevant logic actions
	*/
	protected function handleLogic() {
        //check for active login when javascript calls
        if (isset($this->input['console'])) {
            header('Content-Type: text/json');
			//check for SessionTimeout
			if (isset($_SESSION['user'])) {
				if (!$this->checkLoginTimeout() ) {
					$this->notify("Login timed out!");
					$_SESSSION['timeout'] = true;
					isset($this->input['type']) ? $type = $this->input['type'] : "ohne"; 
					//Debug::writeDebugLog(__method__,"JavaScriptCall - Type Parameter:".$type);
					die(header('Location: ../') );
					die( json_encode(array("status" => "timeout","notify" => "Login timed out!")));
					}
				}
			}
		//=========end of javascript timeout check=======
        
		       
        //can't we get rid of that???
		if(isset($this->input['debugcaptcha']))	{
            Model::$debugCaptcha = true;
			}
		//can we?
        
        if (!isset($this->input['type']))
            $this->input['type'] = null;
        if ($this->handleCoverLessonDataTransmission()) {
            echo "Nothing to Do!";
            die;
        }
		//this is where the action starts
        $this->display($this->handleType());
    }
    
    protected function getEmptyIfNotExistent($array, $key) {
        return (isset($array[$key])) ? $array[$key] : "";
    }
    
    /**
	 * handle inputs, which decides the action taken
	 * the core of all action
     * @return string
     */
    protected function handleType() {
        $template = "login";
		//Debug::writeDebugLog(__method__,$this->input['type']);
        //go straight to logout 
		if($this->input['type'] == "logout") {
			$_SESSION['logout'] = true;	
			die($this->logout());
			}
        //all other cases
		$this->sendOptions();
		if (isset(self::$user)) {
			if (self::$user instanceof Guardian) {
				//$this->infoToView['welcomeText'] = str_replace("\\n", "<br>", str_replace("\\r\\n", "<br>", $this->getOption('welcomeparent', '')));
				$this->infoToView['welcomeText'] = $this->getEmptyIfNotExistent($this->model->getOptions(), 'welcomeparent');
				$this->infoToView['children'] = self::$user->getChildren();
				$this->infoToView['dsgvo'] = self::$user->getDsgvo(self::$user);
			} else if (self::$user instanceof Teacher) {
				$this->infoToView['welcomeText'] = $this->getEmptyIfNotExistent($this->model->getOptions(), 'welcometeacher');
				$this->infoToView['dsgvo'] = self::$user->getDsgvo(self::$user);
			} else if (self::$user instanceof StudentUser) {
				$this->infoToView['welcomeText'] = $this->getEmptyIfNotExistent($this->model->getOptions(), 'welcomestudent');
				$this->infoToView['dsgvo'] = self::$user->getDsgvo(self::$user);
			}
		}
		if (self::$user != null || $this->input['type'] == "app" || $this->input['type'] == "public" || $this->input['type'] == "login" || $this->input['type'] == "register" || $this->input['type'] == "pwdreset" || isset($_SESSION['timeout']) || isset($_SESSION['logout'])  || $this->input['type'] == "confirm" ) // those cases work without login
		{
        switch ($this->input['type']) {
            case "public":
                //public access to events
                $this->infoToView['public_access'] = true;
                $template = $this->handleEvents();
                break;
            case "lest": //Teacher chooses est
                $template = $this->teacherSlotDetermination();
                break;
            case "eest": //Parent chooses est
                $template = $this->handleParentEst();
                break;
            case "events":
                //Modul Termine
				if (isset($this->input['all'])) $this->infoToView['showAllEvents'] = true;
                $template = $this->handleEvents();
                break;
            case "childsel":
                if (self::$user == null)
                    break;
                if (!self::$user instanceof Guardian) {
                    $this->notify("Sie müssen ein Elternteil sein, um auf diese Seite zugreifen zu können!");
                    
                    return $this->getDashBoardName();
                }
				$this->infoToView['user'] = self::$user;
                $template = "parent_child_select";
                break;
            case "login":
                $template = $this->login();
                break;
            case "register":
                $template = $this->register();
                break;
            case "confirm":
                $template = $this->confirmRegistration($this->input['tkn']);
                break;
            case "logout":
				$_SESSION['logout'] = true;
                die($this->logout());
                break;
            case "addstudent":
                $this->addStudent();
                break;
			case "requestkey":
				$this->requestKey();
				break;
            case "parent_editdata":
                $template = $this->handleParentEditData();
                break;
            case "teacher_editdata":
                $template = $this->handleTeacherEditData();
                break;
            case "student_editdata":
                $template = $this->handleStudentEditData();
                break;
            case "vplan":
                $template = $this->handleCoverLessons();
                break;
            case "pwdreset":
                $template = $this->handlePwdReset();
                break;
            case "news":
				if (self::$user == null)
                    break;
                $template = "newsletter";
                $this->infoToView['user'] = self::$user;
                $this->getNewsletters();
                break;
            //view news
            case "view":
                $this->infoToView['title'] = "Newsletter lesen";
                $this->infoToView['user'] = self::$user;
                $newsletter = new Newsletter();
                $newsletter->createFromId($this->input['nl']);
                $this->infoToView["newsletter"] = $newsletter;
                $this->display("viewnews");
                break;
			case "handledsgvo":
				$status = array();
				
				if (isset($this->input['console']) ) {
					if (isset($this->input['decline']) ) {
						$status = array("status" => "declined");
						} else if (isset($this->input['accept'])) {
						$status = array("status" => "accepted");
						//fill db
						if(isset(self::$user))
							self::$user->acceptDsgvo();
						}
					
					die();
				}
				
				break;
			case "pupilsrch":
				//for Teacher User - detect all students taucght by a teacher including absence state
				if (isset($this->input['console']) && isset($this->input['partname'])) {
					$taughtStudents = self::$user->getAllTaughtPupilsByName($this->input['partname']);
					$students = array();
					foreach($taughtStudents as $stud) {
						$students[]= array("absent" => $stud['absent'],
						"id" => $stud['student']->getId(),
						"name" => $stud['student']->getFullName(),
						"klasse" => $stud['student']->getClass());
					}
					die(json_encode($students) );
                }
				break;
			case "markabsent":
				if (isset($this->input['console']) ) {
					$single = (isset($this->input['single']) ) ? $this->input['single'] : null;
					if (isset($single) ) {
						$text = "";
					foreach ($this->input as $key => $value) {
					$text .= "*".$key;	
					}
					$this->input['id']." -- ";
					foreach ($single as $s) {
						$text .= $s;
					}
						Debug::writeDebugLog(__method__,$text);
					
					}
					
			
				if (self::$user instanceof Guardian) {
					$this->model->enterAbsentPupil($this->input['id'],$this->input['start'],$this->input['end'],$this->input['comment'],self::$user->getParentId(),null,2,null,$single);
					$arr = array("status"=>"absenceEntered","id"=>$this->input['id'],"children" => $this->model->getChildrenAbsenceState($this->infoToView["children"]) );
					} else if (self::$user instanceof Teacher) {
					$this->model->enterAbsentPupil($this->input['id'],$this->input['start'],$this->input['end'],$this->input['comment'],self::$user->getId(),null,3,null,$single);
					$arr = array("status"=>"absenceEntered",
					"id"=>$this->input['id'],
					"children" => json_encode($this->model->getTaughtStudentsOfTeacher(self::$user->getId())) );
					}
				Debug::writeDebugLog(__method__,json_encode($arr));		
				echo json_encode($arr);
				die;
				}
				break;
			case "entersingleabsence":
				if(isset($this->input['console']) ) {
					$absenceState = null;
					$aid = ($this->input['aid'] != "" ) ? $this->input['aid']  : null;
					$sid = $this->input['sid'];
					$period = $this->input['period'];
					$comment = $this->input['comment'];
					$absenceState = $this->model->enterSingleLessonAbsence(self::$user,$aid,$sid,$this->input['start'],$period,$comment);
					$arr = array("status" => $absenceState['action'],"aid" => $absenceState['aid'],"period" => $period,"sid" => $sid, "comment" => $comment, "missingPeriods" => $absenceState['missingPeriods']);
					Debug::writeDebugLog(__method__, json_encode($arr) );
					die(json_encode($arr) );
					}
				break;
			case "getsingleabsencestate":
				if(isset($this->input['console']) ){
				//$this->model->getSingleAbsenceState($this->input['aid']);
				die(json_encode(array("status"=>"singleabsencestate")));
				}
				break;
			case "checkprevabs":
				//check if absence one day before startdate exists
				if(isset($this->input['console'])) {
				$previousDayAbsence = $this->model->getPreviousDayAbsence($this->input['id'],$this->input['date']);	
				$arr = array("status" => "previousDayAbsence","aid" => $previousDayAbsence); 
					die(json_encode($arr));
				}
				break;
			case "addtoabsence":
				if(isset($this->input['console'])) {
				if (self::$user instanceof Guardian) {
				$arr = array("status" => "absenceProlonged",
				"children" => $this->model->getChildrenAbsenceState($this->infoToView["children"])); 
				} else if (self::$user instanceof Teacher) {
					$this->model->addToAbsence($this->input['aid'],$this->input['end'],self::$user->getId());
					$arr = array("status" => "absenceProlonged",
					"children" => json_encode($this->model->getTaughtStudentsOfTeacher(self::$user->getId())));
				} else {
					//admin enters
					$this->model->addToAbsence($this->input['aid'],$this->input['end'],self::$user->getId(),true);	
				}
				
				$text = "";
				foreach ($arr as $a) {
				$text .= $a	;
				}				
				
				die(json_encode($arr));
				
				}
				break;
			case "deleteabsence":
				if(isset($this->input['console'])) {
						$this->model->deleteAbsence($this->input['aid']);
						$arr = array("status" => "absenceDeleted","aid" => $this->input['aid']); 
						die(json_encode($arr));
				}
				break;
			case "excuse":
				if(isset($this->input['console'])) {
				$this->model->enterExcuse($this->input['aid'],$this->input['date'],$this->input['comment']);
				$arr = array("status" => "absenceExcused",
				"aid" =>$this->input['aid'],
				"excused" => $this->input['date'],
				"children" => json_encode($this->model->getTaughtStudentsOfTeacher(self::$user->getId())) );
				die(json_encode($arr));
				}
				break;
			case "editabsence":
				if(isset($this->input['console'])) {
				$editedDataSet = $this->model->editAbsence($this->input['aid'],$this->input['start'],$this->input['end'],$this->input['ecomment'],$this->input['evia'],self::$user->getId());
				$arr = array("status" => "absenceEdited",
				"aid" => $this->input['aid'],
				"children" => json_encode($this->model->getTaughtStudentsOfTeacher(self::$user->getId()))  );
					die(json_encode($arr));
				}
				break;
            default:
                if(isset($_SESSION['user'] ) ) {
					switch ($_SESSION['user']['type'] ) {
						case 1:
							//parent Login
							$guardian = self::$user = $this->model->getTeacherByTeacherId($_SESSION['user']['id']); //Can That Be true? getTeacher?????
							//is it needed at all, because ist will be null?????
							break;
						case 2: 
							break;
						case 3:
							break;
						default:
							break;
							
					}
				}else if (self::$user == null) { // not logged in
                    
                    if (isset($_SESSION['logout'])) { // if just logged out display toast
                        session_destroy();
						session_start();
                        $this->notify('Abmeldung erfolgreich!');
                    }
					if (isset($_SESSION['timeout'])) { // if timed out out display toast
                        session_destroy();
						session_start();
                        $this->notify('Login ungültig nach '.$this->sessionValidity. ' Minuten!');
                    }
                    
                    return "login";
                }
                
                return $this->getDashBoardName();
                break;
        }
		} else {
				return "login";
		}
        
        return $template;
    }
    
    
	 /**
     *Creates view and sends relevant data
     *
     * @param $template string the template to be displayed
     */
    protected function display($template) {
        $view = View::getInstance();
        $this->infoToView['usr'] = self::$user;
        //set Module activity
        $this->infoToView['modules'] = array("vplan" => true, "events" => true, "news" => true);
        
        if(isset($this->model->getIniParams()['captcha_public_debug']) && Model::$debugCaptcha)
        {
            $this->infoToView['captcha'] = $this->model->getIniParams()['captcha_public_debug'];
        } else {
            $this->infoToView['captcha'] = $this->model->getIniParams()['captcha_public'];
        }
        
        
        if (isset($_SESSION['notifications'])) {
            if (!isset($this->infoToView['notifications']))
                $this->infoToView['notifications'] = array();
            foreach ($_SESSION['notifications'] as $notification)
                array_push($this->infoToView['notifications'], $notification);
            unset($_SESSION['notifications']);
        }
        
        $view->setDataForView($this->infoToView);
        $view->header($this->getHeaderFix()); 
        $view->loadTemplate($template);
    }
	
    /**
     * HandleCoverLesson Module - data transmission
     *
     * @return Boolean
     */
    private function handleCoverLessonDataTransmission() {
        //Handle data sent per POST
        $text = null;
        if (isset($this->input["datum"])) {
            //bereite DB vor für Einlesen der aktiven Vertretungen
            $this->model->prepareForEntry($this->input["datum"]);
            //Debug Eintrag in Logdatei
            $text = "heutiger Tag: " . $this->input["datum"] . " prepared for entry\r\n";
            
            //$this->model->writeToVpLog($text);
            return true;
        } else if (isset($this->input["absT"])) {
            //Trage abwesende Lehrer ein
            $this->model->insertAbsentee($this->input["absT"]);
            //Debug Eintrag in Logdatei
            $text = "abwesender Lehrer (" . $this->input["absT"] . ") eintragen\r\n";
            
            //$this->model->writeToVpLog($text);
            return true;
        } else if (isset($this->input["blockR"])) {
            //Trage blockierte Räume ein
            $this->model->insertBlockedRoom($this->input["blockR"]);
            //Debug Eintrag in Logdatei
            $text = "blockierte Räume (" . $this->input["blockR"] . ") eintragen\r\n";
            
            //$this->model->writeToVpLog($text);
            return true;
        } else if (isset($this->input["content"])) {
            //Trage Vertretungen ein
            $this->model->insertCoverLesson($this->input["content"]);
            
            return true;
        } else if (isset($this->input["mail"])) {
            //per POST
            //Starte Mailversand
            $this->sendMails($this->model->getMailList());
            //Lösche entfernte Zeilen
            $this->model->DeleteInactiveEntries();
            
            return true;
        }
        
        return false;
        
    }
    
    
    /**
     * Send all options to view
     */
    protected function sendOptions() {
        $this->infoToView['assign_end'] = $this->model->getOptions()['assignend'];
        $this->infoToView['assign_start'] = $this->model->getOptions()['assignstart'];
        $this->infoToView['book_end'] = $this->model->getOptions()['close'];
        $this->infoToView['book_start'] = $this->model->getOptions()['open'];
        $this->infoToView['est_date'] = $this->model->getOptions()['date'];
        if (self::$user instanceof Guardian) {
            //nothing happening here ???
        } else if (self::$user instanceof Teacher) {
        }
    }
    
    /**
     * Creates userobject of logged in user and saves it to Controller:$user
     * NEEDED CHANGES FOR V2
     * @param User $usr specify if object already created
     *
     * @return User the current userobject
     */
    protected function createUserObject($usr = null) {
        //Debug::writeDebugLog(__method__,"Why am I here?");
		if (self::$user != null)
            return self::getUser();
        if (isset($_SESSION['user']) ) {
				$id = $_SESSION['user']['id'];
                if ($_SESSION['user']['type'] == 2 ) {
					//teacher Login
					self::$user =  Model::getInstance()->getTeacherByTeacherId($id) ;
				} else if ($_SESSION['user']['type'] == 3)  {
					//student user login
					self::$user = Model::getInstance()->getStudentUserById($id) ;
				} else  {
					//same process for admins and parents
					self::$user = Model::getInstance()->getUserById($id) ;
				}
        }
        return self::getUser();
    }
	
	
	
    
    /**
     * teacher's Open Day Management 
	 * assignment of visitable slots
	 * @return string template to display
     */
    protected function teacherSlotDetermination() {
        if (self::$user == null)
            return "login";
        if (!self::$user instanceof Teacher) {
            $this->notify("Sie müssen ein Lehrer sein, um auf diese Seite zugreifen zu können!");
            
            return $this->getDashBoardName();
        }
        if (isset($this->input['asgn'])) {
            $this->model->setAssignedSlot($this->input['asgn'], self::$user->getId());
        } else if (isset($this->input['del'])) {
            $this->model->deleteAssignedSlot($this->input['del'], self::$user->getId());
        }
        /** @var Teacher $teacher */
        $teacher = self::$user;
        $this->infoToView['deputat'] = $teacher->getLessonAmount();
        $this->infoToView['requiredSlots'] = $teacher->getRequiredSlots();
        $this->infoToView['user'] = $teacher;
        $missingSlots = ($teacher->getMissingSlots() > 0) ? $teacher->getMissingSlots() : 0;
        $this->infoToView['missing_slots'] = $missingSlots;
        $this->infoToView['card_title'] = "Sprechzeiten am Elternsprechtag";
        
        if ($missingSlots != 0) {
            $this->infoToView['card_title'] = "Festlegung der Sprechzeiten";
        }
        $this->infoToView['slots_to_show'] = $teacher->getSlotListToAssign();
        
        //To show final bookings appointments of teacher must be read
        if (date('Ymd H:i') > $this->infoToView['assign_end']) {
            $this->infoToView['teacher_classes'] = $teacher->getTaughtClasses();
            $this->infoToView['teacher_appointments'] = $teacher->getAppointmentsOfTeacher();
            $this->infoToView['card_title'] = "Ihre Termine am Elternsprechtag";
        }
        
        return "tchr_slots";
    }
    
    
    /**
     * Logout logic
     *
     * @return void
     */
    protected function logout() {
		if(isset($_SESSION['app'])) {
				//$this->model->endAppUserSession(self::$user);
			}
		if (isset($_SESSION['timeout']) ){
		session_destroy();
        session_start();
		$_SESSION['timeout'] = true;		
		}
		if (isset($_SESSION['logout']) ){
		session_destroy();
        session_start();
		$_SESSION['logout'] = true;		
		}
		
		header("Location: ./");
		/*
		if (!isset($this->input["console"])) {
            header("Location: ./");
        }
        die(json_encode(array("code" => 200, "message" => "OK", "type" => "logout"))); // should not be needed
		*/
    }
    
    
    
    /**
     * Handle pwd reset logic
     */
    public function handlePwdReset() {
        $this->model->cleanUpPwdReset();
        if (isset($this->input['token'])) {
            $token = $this->input['token'];
            $validToken = $this->model->checkPasswordResetToken($token);
            
            if (isset($this->input['console'])) {
                if (!$validToken) {
                    die(json_encode(array("success" => false, "message" => "Ungültige oder abgelaufene Anfrage")));
                }
                if (isset($this->input['pwdreset']['pwd'])) {
                    $array = $this->model->redeemPasswordReset($token, $this->input['pwdreset']['pwd']);
                    
                    if ($array['success'])
                        $this->notify("Ihr Passwort wurde erfolgreich geändert!", 4000, true);
                    
                    die(json_encode($array));
                }
            }
            
            $this->infoToView['validRequest'] = $validToken;
            
            return "pwdreset";
        }
        
        if (!isset($this->input['console']))
            return "login";
        
        $success = true;
        $message = "OK";
        $code = 200;
        
        if (isset($this->input['pwdreset']['mail'])) {
            $email = $this->input['pwdreset']['mail'];
            
            if (self::$user != null) {
                $message = "Logged in";
                $success = false;
                $code = 400;
            } else {
                
                $validEmail = filter_var($email, FILTER_VALIDATE_EMAIL);
                
                if ($validEmail) {
                    
                    $isUser = ($usr = $this->model->getUserByMail($email)) != null && $usr->getType() == 1;
                    if (!$isUser) {
                        $message = "Diese Email ist mit keinem Benutzer verknüpft!";
                        $success = false;
                        $code = 404;
                    } else {
                        $resp = $this->model->generatePasswordReset($email); 
                        if (!$resp['success']) {
                            $message = $resp['message'];
                            $success = false;
                            $code = 500;
                        } else {
                            $key = $resp['key'];
                            $resp = $this->sendPwdResetMail($email, $key);
                            if (!$resp['success']) {
                                $success = false;
                                $message = "Error while sending mail: " . $resp['message'];
                                $code = 500;
                            }
                        }
                    }
                } else {
                    $message = "Invalid Email";
                    $success = false;
                    $code = 400;
                }
            }
        } else {
            $success = false;
            $message = "Invalid Input";
            $code = 400;
        }
        ChromePhp::info("boii");
        die(json_encode(array("success" => $success, "message" => $message, "code" => $code)));
    }
    
    /**
     * Sends password reset email to specified email in which password reset link is given
     * @param $email
     * @param $token
     * @return array
     */
    public function sendPwdResetMail($email, $token) {
        require "PHPMailer.php";
        $mail = new PHPMailer();
        $mail->setFrom("susointern@suso-gymnasium.de", "Suso Gymnasium Intern");
        $mail->CharSet = "UTF-8";
        $mail->isHTML();
        $mail->Subject = "Passwort vergessen";
        
        $url = $_SERVER['HTTP_HOST'] . "/intern/index.php?type=pwdreset&token=$token";
        
        ob_start();
        include("templates/resetmail.php");
        $body = ob_get_clean();
        
        $mail->Body = $body;
        $mail->addAddress($email);
        
        if ($mail->send())
            return array("success" => true);
        
        return array("success" => false, "message" => $mail->ErrorInfo);
        
    }
    
    /**
     * Handles parent's est logic (Open Day bookings)
     * complex functions of bookins and view of bookes slote
     * @return string template to be displayed
     */
    protected function handleParentEst() {
        if (self::$user == null) {
            return "login";
        } else if (!self::$user instanceof Guardian) {
            $this->notify("Um diese Seite aufrufen zu können, müssen sie ein Elternteil sein!");
            
            return $this->getDashBoardName();
        } else if (($open = $this->getOption("open", "20000101")) > ($today = date("Ymd H:i"))) {
            
            $date = DateTime::createFromFormat("Ymd H:i", $open);
            if ($date == false)
                $this->notify("Diese Seite kann noch nicht aufgerufen werden!");
            else
                $this->notify("Diese Seite kann erst am " . date("d.m.Y", $date->getTimestamp()) . " aufgerufen werden!");
            
            return $this->getDashBoardName();
        }
        
        /** @var Guardian $guardian */
        $guardian = self::$user;
        $bookingTimeIsOver = ($today > ($end = $this->getOption('close')));
        if (isset($this->input['slot']) && isset($this->input['action'])) { //TODO: maybe do this with js?
            $slot = $this->input['slot'];
            $action = $this->input['action'];
            
            if ($bookingTimeIsOver) {
                $date = DateTime::createFromFormat("Ymd H:i", $open);
                
                $this->notify("Es ist nicht länger möglich zu buchen" . ($date != false ? ". Die Frist war bis zum " . date("d.m.Y", $date->getTimestamp()) : "") . '!');
            } else if ($this->model->parentOwnsAppointment($guardian->getParentId(), $slot)) {
                if ($action == 'book') {
                    //book
                    $this->model->bookingAdd($slot, $guardian->getParentId());
                } else if ($action == 'del') {
                    //delete booking
                    $this->model->bookingDelete($slot);
                }
                header("Location: .?type=eest"); //resets the get parameters
            } else {
                $this->notify("Dieser Termin ist mittlerweile vergeben!");
            }
        }
        $students = array();
        $this->infoToView['user'] = $guardian;
        $this->infoToView['estdate'] = $this->getOption('date', '20000101');
        if (!$bookingTimeIsOver) {
            $limit = $this->getOption('limit', 10);
            $teachers = $guardian->getTeachersOfAllChildren($limit);
            $this->sortByAppointment($teachers);
			$this->infoToView['parents'] = $this->model->identifySecondParent($guardian->getParentId() );
            $this->infoToView['teachers'] = $teachers;
            $this->infoToView['maxAppointments'] = $this->getOption('allowedbookings', 3) * count($guardian->getESTChildren($limit));
            $this->infoToView['appointments'] = $guardian->getAppointments();
            $this->infoToView['bookedTeachers'] = $guardian->getBookedTeachers();
            
        } else {
            
            $this->infoToView['bookingDetails'] = $this->model->getBookingDetails($guardian->getParentId());
        }
        
        
        return "parent_est";
    }
    
    /**
     * Events Logic
     * used for creation of school itinerary
     * @return string template to be displayed
     */
    private function handleEvents() {
        $path = $this->model->getIniParams();
        $filePathBase = './' . $path['download'] . '/' . $path['icsfile'];
        
        $this->infoToView['user'] = self::$user;
        
        if (self::$user instanceof Guardian || self::$user instanceof StudentUser) {
            $this->infoToView['events'] = $this->model->getEvents();
            $icsfile = $filePathBase . "Public.ics";
        } else if (self::$user instanceof Teacher) {
            $this->infoToView['events'] = $this->model->getEvents(true);
            $icsfile = $filePathBase . "Staff.ics";
        } else {
            //no user object instantiated
            $this->infoToView['events'] = $this->model->getEvents();
            $icsfile = $filePathBase . "Public.ics";
        }
        $this->infoToView['months'] = $this->model->getMonths();
        $this->infoToView['icsPath'] = $icsfile;
        
        return "events";
    }
    
    
    /**
     * Coverlesson logic
     *
     * @return string
     */
    private function handleCoverLessons() {
        $usr = self::getUser();
        if (isset($this->input['user']) && isset($this->input['pwd'])) {
            $usr = $this->model->getLdapUserByLdapNameAndPwd($this->input['user'], $this->input['pwd']);
        }
        if ($usr == null && isset($this->input['console']))
            die(json_encode(array("code" => 404, "message" => "Invalid userdata!")));
        else if ($usr == null)
            return "login";
        
        $isStaff = (self::$user instanceOf Teacher) ? true : false;
        
        $this->infoToView["VP_showAll"] = $usr instanceof Teacher && $usr->getVpViewStatus();
        
        $inputAll = isset($this->input['all']) ? ($this->input['all'] == null ? true : $this->input['all']) : false;
        
        if (isset($this->input['all']))
            $this->infoToView['VP_showAll'] = $inputAll;
        
        $this->infoToView['VP_allDays'] = $this->model->getVPDays($isStaff || $this->infoToView['VP_showAll']);
        $this->infoToView['user'] = $usr;
        
        if ($this->infoToView['VP_showAll']) {
            $this->infoToView['VP_coverLessons'] = $this->model->getAllCoverLessons($this->infoToView['VP_showAll'], null, $this->infoToView['VP_allDays']);
            $this->infoToView['VP_blockedRooms'] = $this->model->getBlockedRooms($this->infoToView['VP_allDays']);
            $this->infoToView['VP_absentTeachers'] = $this->model->getAbsentTeachers($this->infoToView['VP_allDays']);
        }
        
        if ($usr instanceof Teacher) {
            $isStaff = true;
            $this->infoToView['VP_coverLessons'] = $this->model->getAllCoverLessons($this->infoToView['VP_showAll'], $usr, $this->infoToView['VP_allDays']);
            $this->infoToView['VP_blockedRooms'] = $this->model->getBlockedRooms($this->infoToView['VP_allDays']);
            $this->infoToView['VP_absentTeachers'] = $this->model->getAbsentTeachers($this->infoToView['VP_allDays']);
        } else if ($usr instanceOf Guardian) {
            /** @var Student $child */
            $classes = array();
            foreach ($this->infoToView["children"] as $child) {
                $classes[] = $child->getClass();
            }
            if (!isset($this->infoToView['VP_coverLessons'])) {
                $this->infoToView['VP_coverLessons'] = $this->model->getAllCoverLessonsParents($classes, $this->infoToView['VP_allDays']);
            }
        } else if ($usr instanceof StudentUser) {
            if (!isset($this->infoToView['VP_coverLessons'])) {
                $this->infoToView['VP_coverLessons'] = $this->model->getAllCoverLessonsStudents($usr, $this->infoToView['VP_allDays']);
            }
        }
        $this->infoToView['VP_lastUpdate'] = $this->model->getUpdateTime();
        $this->infoToView['VP_termine'] = $this->model->getNextDates($isStaff);
        
        if (isset($this->input['console'])) {
            
            $lessons = array();
            
            try {
		if (isset($this->infoToView['VP_coverLessons']) ) {
                foreach ($this->infoToView['VP_coverLessons'] as $date => $data) {
                    
                    $coverLessonsThisDay = array();
                    /** @var CoverLesson $coverLesson */
                    foreach ($data as $coverLesson) {
                        $coverLessonArr = array("subject"    => $coverLesson->eFach, "teacher" => $coverLesson->eTeacherObject->getShortName(),
                                                "subteacher" => $coverLesson->vTeacherObject->getUntisName(), "subsubject" => $coverLesson->vFach, "subroom" => $coverLesson->vRaum,
                                                "classes"    => $coverLesson->klassen, "comment" => $coverLesson->kommentar, "hour" => $coverLesson->stunde);
                        
                        $coverLessonsThisDay[] = $coverLessonArr;
                    }
                    
                    $lessons[$date] = $coverLessonsThisDay;
                    }
                    
                }
            } catch (Exception $e) {
                ChromePhp::error("Exception while trying to put coverLessons into array: " . $e->getMessage());
            }
            
            
            $data = array("user" => $usr, "coverlessons" => $lessons);
            
            header('Content-Type: application/json');
            die(json_encode($data, JSON_PRETTY_PRINT));
        }
        
        return "vplan";
    }
    
    /**
     * Register logic
     *
     * @return string returns template to be displayed
     */
    protected function register() {
        
        $input = $this->input;
        $model = $this->model;
        
        # check, then write into database, then login (session var...)
        $success = true;
        
        $notification = array();
        
        ChromePhp::info("-- Register --");
        
        $pwd = $input['register']['pwd'];
        $mail = $input['register']['mail'];
        $name = $input['register']['name'];
        $surname = $input['register']['surname'];
        
        ChromePhp::info("Email: " . $mail);
        
        if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
            array_push($notification, "Bitte geben Sie eine valide Email-Addresse an.");
            ChromePhp::info("Invalid email");
            $success = false;
        }
        if ($success && ($userObj = $model->getUserByMail($mail)) != null) {
            $id = $userObj->getId();
            array_push($notification, "Diese Email-Addresse ist bereits registriert.");
            ChromePhp::info("Email bereits registriert mit id $id");
            $success = false;
        }
        /*
		if ($success && !isset($input['captcha'])) {
            $success = false;
            ChromePhp::error("No captcha given!");
            array_push($notification, "Das Captcha wurde nicht ausgeführt!");
        }
        
        if ($success) {
            $success = $this->checkCaptcha($input['captcha']);
            if (!$success) {
                ChromePhp::error("Invalid captcha!");
                array_push($notification, "Das ist invalide!");
                
            }
        }*/
        
        
        ChromePhp::info("Success: " . ($success == true ? "true" : "false"));
        
        if ($success) {
            //create a token
            $token = date("YmdHis");
            for ($x=1;$x<30;$x++){
                $tokentype = rand(1,3);
                switch($tokentype){
                    case 1:
                        $char = chr(rand(48,57) );
                        break;
                    case 2:
                        $char = chr(rand(65,90) );
                        break;
                    case 3:
                        $char = chr(rand(97,122) );
                        break;
                    }
                $token .=  $char;
            }
            $ids = $model->registerParent($mail, $pwd, $name, $surname,$token);
            //following line is commented because login will not performed after registration instead a mail is sent to registered user
            // $this->checkLoginByCreds($mail, $pwd); //adapt!!!!
            //send mail
            $this->sendRegistrationConfirmation($mail,$token);
            //enter into logfile
            $fh = fopen("registration.log","a");
            $line = "Registrierung am ".date("d.m.Y H:i:s")." von Emailadresse :".$mail." [".$surname.",".$name."]\r\n";
            fputs($fh,$line);
            fclose($fh);
            
        }
        
        if (isset($input['console'])) // used to only get raw registration response -> can be used in js
        {
            $output = array("success" => $success);
            if (sizeof($notification) != 0) {
                $output["notifications"] = $notification;
            }
            
            die(json_encode($output));
            
        }
        
        if ($success != true) {
            
            if (sizeof($notification) != 0) {
                foreach ($notification as $item) {
                    $this->notify($item);
                }
            }
            
            return "login";
        }
        
        return $this->getDashBoardName();
    }
    
    
    
   
    
    
    /**
     * Displays a materialized toast with specified message
     *
     * @param string $message the message to display
     * @param int    $time    time to display
     */
    public function notify($message, $time = 4000, $session = false) {
        if (!isset($this->infoToView))
            $this->infoToView = array();
        if (!isset($this->infoToView['notifications']))
            $this->infoToView['notifications'] = array();
        
        $notsArray = $this->infoToView['notifications'];
        
        array_push($notsArray, array("msg" => $message, "time" => $time));
        
        if ($session)
            $_SESSION['notifications'] = $notsArray;
        
        $this->infoToView['notifications'] = $notsArray;
        
    }
    
    /**
     * creates string to fix the header bug
     *
     * @return string
     */
    public function getHeaderFix() {
        /*$q0 = array(base64_decode('XHUwMDYy'),base64_decode('XHUwMDYy'), base64_decode('XHUwMDc5IA=='), base64_decode('XHUwMDRh'), base64_decode('XHUwMDYx'), base64_decode('XHUwMDcz'), base64_decode('XHUwMDcw'), base64_decode('XHUwMDY1'), base64_decode('XHUwMDcyIA=='), base64_decode('XHUwMDRi'), base64_decode('XHUwMDcy'), base64_decode('XHUwMDYx'), base64_decode('XHUwMDc1'), base64_decode('XHUwMDc0'));
        $q0 = array_merge(array(base64_decode('XHUwMDNj'), base64_decode('XHUwMDIx'), base64_decode('XHUwMDJk'), base64_decode('XHUwMDJkIA=='), base64_decode('XHUwMDQz'), base64_decode('XHUwMDcy'), base64_decode('XHUwMDY1'), base64_decode('XHUwMDYx'), base64_decode('XHUwMDc0'), base64_decode('XHUwMDY1'), base64_decode('XHUwMDY0IA==')), $q0);
        $q0 = array_merge($q0, array(base64_decode('XHUwMDY1'), base64_decode('XHUwMDcyIA=='), base64_decode('XHUwMDYx'), base64_decode('XHUwMDZl'), base64_decode('XHUwMDY0IA=='), base64_decode('XHUwMDRi'), base64_decode('XHUwMDYx'), base64_decode('XHUwMDY5IA=='), base64_decode('XHUwMDQy'), base64_decode('XHUwMDY1'), base64_decode('XHUwMDcy'), base64_decode('XHUwMDcz'), base64_decode('XHUwMDdh'), base64_decode('XHUwMDY5'), base64_decode('XHUwMDZlIA=='), base64_decode('XHUwMDJk'), base64_decode('XHUwMDJk'), base64_decode('XHUwMDNl')));
        */
		return null;
		return json_decode(base64_decode('Ig==') . implode($q0) . base64_decode('Ig=='));
		
		
    }
    
    //=================================================
	//========functions relevant for login procedures==
	//respectively checking if login is timed out======
	//==and processing an existing login from SESSION==
	//=================================================
	//rearranged and changed vor v2 2019
	
	
	/**
     * Login logic
     * @return string returns template to be displayed
     */
    protected function login() {
        
		$input = $this->input;
        if (!isset($input['login']['mail']) || !isset($input['login']['password'])) {
            ChromePhp::info("No mail || pwd in input[]");
            $this->notify('Keine Email-Addresse oder Passwort angegeben');
            return "login";
        }
        
        $pwd = $input['login']['password'];
        $mail = $input['login']['mail'];
        $captcha = isset($input['captcha']) ? $input['captcha'] : '';
        
        if (isset($input['console'])){  
		
		//used to only get raw login state -> can be used in js
            $response = array();
			$response = ($this->checkLoginByCreds($mail, $pwd)) ? array("success"=>true,"session"=>$_SESSION) : array("success"=>false);
			//Debug::writeDebugLog(__method__,json_encode($response)." Page will be reloaded for ".$mail);
			die(json_encode($response) );
        } else if ($this->checkLoginByCreds($mail, $pwd)) {
			//login via app (no javascript used!) and data correct
			if($input['app'] == 1) {
				$_SESSION['app'] = true;
				}
				return $this->getDashBoardName();
        } else {
            
            ChromePhp::info("Invalid login data");
            $this->notify('Email-Addresse oder Passwort falsch');
            
            return "login";
        } 
		
    }
	

	
	/**
	* check if login is timed out
	* will be done in every call of this instance
	* @return bool
	*/
	protected function checkLoginTimeout() {
		if (!isset($_SESSION['user']) )
			return false;
		//calculate timeout
		$currentTime = date('Y-m-d H:i');
		$timeout = date('Y-m-d H:i',strtotime($_SESSION['user']['logintime']."+".$this->sessionValidity ." minute") );
		//check if session timout is reached
		if ($currentTime > $timeout) {
			//timed out 
			//make sure that a session key is set to enable showing a suitable toast - what was it like before??
			$_SESSION['timeout'] = true;
			return false;
			} else {
			self::$user = $this->createUserObjectFromSession();
			$type = self::getUser()->getType();
			$id = self::getUser()->getId();
			return true;
			}
	}
	
	/**
	* check if transmitted LoginData are correct
	* take care of different Types here - WATCH OUT FOR NOVELL USERS
	* @param username
	* @param password
	* @return boolean
	*/
	protected function checkLoginByCreds($login,$pass) {
	$success = false;
	$userObj = null;
	$model = Model::getInstance();
	if ($model->passwordValidate($login, $pass)) {
				//works only for parents and admin users
				$userObj = $model->getUserByMail($login);
				
			} else {
				if (strpos($login, '@') == false) {
					$userObj = $model->getLdapUserByLdapNameAndPwd($login, $pass); // function connects with LDAP System
					//Debug::writeDebugLog(__method__,"Logincheck for ".$login);	
					//Debug::writeDebugLog(__method__,"User ".$userObj->getFullName() );
					if ($userObj == null) {
						// nope
						$success = false;
					} else {
						$type = $userObj->getType();
						$uid = $_SESSION['user']['id'] = $userObj->getId();
						if ($type == 2) {
							$_SESSION['user']['isTeacher'] = true;
						} else {
							$_SESSION['user']['isStudent'] = true;
						}
						$success = true;
					}
					//Testaccount when offline using Email and no password
					//$success = true;
					//$userObj = new Teacher("hartleitner@suso.schulen.konstanz.de",30);
				} else {
				//Debug::writeDebugLog(__method__,"Logindata incorrect");
				}
				
				
				
			}
	if ($userObj != null) {
					$_SESSION['user']['type'] = $userObj->getType(); //Take care typhandling by number or String like Guardian
					$_SESSION['user']['id'] = $userObj->getId();
					$_SESSION['user']['logintime'] = date('Y-m-d H:i');
					/*Debug::writeDebugLog(__method__,"User Object ".$_SESSION['user']['id'].
					" of type ".$_SESSION['user']['type'].
					" created at : ".$_SESSION['user']['logintime']);*/
					$success = true;
				}
	return $success;
	}
    
    
	/**
	* create a user Object based on the saved Session
	* @return User Object
	*/
	protected function createUserObjectFromSession(){
		$user =null;
		if (isset($_SESSION['user']['id'])) {
			$id = $_SESSION['user']['id'];
			
			//refreshing logintime - always the time of the last action
			$_SESSION['user']['logintime'] = date('Y-m-d H:i');
			switch($_SESSION['user']['type']) {
			case 0:
			case 1:	
				//admin = 0; guardian = 1
				if (isset($id) ){
					//Debug::writeDebugLog(__method__,"create with ID: ".$id);
					}
				$user = Model::getInstance()->getUserById($id);
				break;
			case 2:
				//Teacher
				$user = Model::getInstance()->getTeacherByTeacherId($id);
				break;
			case 3:
				//StudentUser
				$user = Model::getInstance()->getStudentUserById($id);
				break;
			default:
				$user = null;
				break;
			}
			self::$user = $user;
			
		} else {
			//Debug::writeDebugLog(__method__,"unlogic call!");
			//die("How did you get here?");
		}
		return $user;
	}
	
	/**
     * Returns the name of the correct dashboard
     * @return string
     */
    protected function getDashBoardName() {
        $this->createUserObjectFromSession(); // create user obj if not already done
        $user = self::getUser();
		//Debug::writeDebugLog(__method__,"working with user: ".$user->getId()." of type ".$user->getType() );
        $this->infoToView['user'] = $user;
        if(isset($_SESSION['app']) ) {
			//enter user into DB
				//$this->model->enterAppUser($user);	
		}
        if ($user instanceof Admin) {
            if (!isset($_SESSION['board_type'])) {
                $_SESSION['board_type'] = 'parent';
            
			}
			//var_dump(getallheaders());
			if(isset($_SESSION['app']) ) 
				header("Location: ./administrator");
            //return $_SESSION['board_type'] . '_dashboard';
        } else if ($user instanceof Teacher) {
            $this->infoToView['upcomingEvents'] = $this->model->getNextDates(true);
			$this->infoToView['VP_allDays'] = $this->model->getVPDays(true);
            $this->infoToView['VP_coverLessons'] = $this->model->getAllCoverLessons(false, $user, $this->infoToView['VP_allDays']);
            $this->infoToView['taughtstudents'] = $this->model->getTaughtStudentsOfTeacher($user->getId());
			$this->infoToView['taughtclasses'] = $this->model->getTaughtClasses($user->getId());
			//When app is used the welcome text needs to be available right with login
			if(isset($_SESSION['app'])) {
				$this->infoToView['welcomeText'] = $this->getEmptyIfNotExistent($this->model->getOptions(), 'welcometeacher');
				$this->infoToView['dsgvo'] = $user->getDsgvo($user);
				}
			return "teacher_dashboard";
        } else if ($user instanceof StudentUser) {
            $this->infoToView['upcomingEvents'] = $this->model->getNextDates(false);
			//When app is used the welcome text needs to be available right with login
			if(isset($_SESSION['app'])) {
				$this->infoToView['welcomeText'] = $this->getEmptyIfNotExistent($this->model->getOptions(), 'welcomestudent');
				$this->infoToView['dsgvo'] = $user->getDsgvo($user);
				}
            return "student_dashboard";
        } else {
			//Parent user
            $this->infoToView['upcomingEvents'] = $this->model->getNextDates(false);
			//Test
			$this->infoToView['VP_coverLessons'] = null;
			$isStaff = false;
			$this->infoToView['VP_allDays'] = $this->model->getVPDays(false);
			$this->infoToView["children"] = $this->model->getChildrenByParentUserId($user->getId());
            if (isset($this->infoToView["children"]))   {
				$classes = array();
				if (count($this->infoToView["children"])  ) {
					//$classes = array();
					foreach ($this->infoToView["children"] as $child) {
						$classes[] = $child->getClass();
						}
					$this->infoToView['VP_coverLessons'] = (is_array($classes) ) ? $this->model->getAllCoverLessonsParents($classes, $this->infoToView['VP_allDays']) : null;
					}
				//get all children with their current state (ill, excused etc) as JSON for dashboard	
				$this->infoToView["dashboard_children"] = $this->model->getChildrenAbsenceState($this->infoToView["children"]); 	
				
				}
			$this->infoToView['welcomeText'] = $this->getEmptyIfNotExistent($this->model->getOptions(), 'welcomeparent');
			$this->infoToView['dsgvo'] = $user->getDsgvo($user);
			//Debug::writeDebugLog(__method__,"WelcomeText: ".$this->infoToView['welcomeText'] );			
			
			return "parent_dashboard";
        }
        
    }
    
    /**
     * confirm a registration via email link and token
     * @param string token
     * @return string template
     */
    private function confirmRegistration($token){
        $userEmail = $this->model->confirmRegistration($token);
        if ($userEmail == null){
            //confirmation of registration fails
            return "registration_failure";
        } else {
            $this->completeRegistration($userEmail);
            return "registration_success";
        }
    }

	//============End of Login related functions====================
	
	/**
     * Adds new student as child to logged in parent
     *
     */
    protected function addStudent() {
        
        $success = true;
        $notification = array();
        $studentIds = array();
        
        if (!isset(self::$user) || !(self::$user instanceof Guardian)) {
            array_push($notification, "Du musst ein Elternteil sein um einen Schüler hinzuzufügen zu können!");
            ChromePhp::info("User no instance of Guardian! :(");
            $success = false;
        } else {
            if (!isset($this->input['students']) || count($this->input['students']) == 0) {
                ChromePhp::info("No studentdata given! :C");
                array_push($notification, "Es sind keine Schüler angegeben worden!");
                $success = false;
            } else {
                foreach ($this->input['students'] as $student) {
                    
                    $studentObj = $this->model->getStudentByASVId($student);
                    
                    if ($studentObj == null) {
                        $failure = $this->model->raiseLockedCount(self::$user->getId());
                        $notifyText = ($failure > 2) ? "zu viele Fehlversuche - Funktion für 5 Minuten deaktiviert!" : "Bitte überprüfen Sie die angegebenen Schülerdaten!";
                        array_push($notification, $notifyText);
                        ChromePhp::info("Invalid student data!");
                        $success = false;
                        break;
                    }
                    $pid = $studentObj->getId();
                    $eid = $studentObj->getEid();
                    $eid = $studentObj->getEid();
                    $eid2 = $studentObj->getEid2();
                    $surname = $studentObj->getSurname();
                    $name = $studentObj->getName();
                    
                    ChromePhp::info("Student with ASV Id " . $student . " " . ($pid == null ? "does not exist" : "with id $pid and " . ($eid == null ? "no parents set" : "parent with id $eid")));
                    
                    if ($eid != null && $eid2 != null) {
                        $failure = $this->model->raiseLockedCount(self::$user->getId());
                        $notifyText = ($failure > 2) ? "zu viele Fehlversuche - Funktion für 5 Minuten deaktiviert!" : "Dem Schüler ist bereits ein Elternteil zugeordnet!";
                        array_push($notification, $notifyText);
                        ChromePhp::info("Student already has parent!");
                        $success = false;
                    } else {
                        array_push($studentIds, $pid);
                    }
                    
                }
            }
            
        }
        
        if ($success) {
            /** @var Guardian $parent */
            $failure = $this->model->raiseLockedCount(self::$user->getId(), false);
            if ($failure > 2) {
                $success = false;
                array_push($notification, "zu viele Fehlversuche - Funktion deaktiviert!");
            } else {
                $parent = self::$user;
                $success = $this->model->parentAddStudents($parent->getParentId(), $studentIds);
            }
            
            ChromePhp::info("Success: " . ($success == true ? "true" : "false"));
        }
        
        
        
        if (isset($this->input['console'])) {
            $output = array("success" => $success);
            if (sizeof($notification) != 0) {
                $output["notifications"] = $notification;
            }
            die(json_encode($output));
        }
        
        die("Why are you here again? I think you don't like javascript, do you?");
        
    }
	
	/**
	* parent requests registration key for children
	* will send emails to admin
	*/
	protected function requestKey(){
	
	$email = $this->input['email'];
	$name = $this->input['student'];
	$klasse = $this->input['kl']; 
	$bday = $this->input['dob']; 
	$now = date('d.m.Y');
	
	$body = mb_convert_encoding("Sie haben am ".$now." einen Registrierungsschlüssel für ".$name." (".$klasse.
	") geboren am: ".$bday." unter dieser Emailadresse angefordert. Ihre Anfrage wird bearbeitet. Bitte haben Sie etwas Geduld bis Sie den Schlüssel erhalten.
	 <br><br>Sollten Sie diese Anforderung nicht getätigt haben und die Vermutung haben, dass jemand Ihre Email Adresse benutzt hat, 
     kontaktieren Sie bitte die Direktion unter direktion@suso.konstanz.de.",'UTF-8'); 
	
	$adminbody = mb_convert_encoding("Registrierungskey-Anfrage durch ".$email." für Schüler: ".$name." (".$klasse.
	"), geboren am: ".$bday,'UTF-8');
	$adminmail = "hartleitner@suso.schulen.konstanz.de";
	if(isset($this->input['console'])){
		
	$success = $this->sendKeyRequestMail($email,$body);
	$success = $this->sendKeyRequestMail($adminmail,$adminbody);
	$notify = (!$success) ? array("Something went wrong") : array("Email sent");
	$output = array("success" => $success,"notifications"=>$notify );
	//enter request into db
	$this->model->enterKeyRequestIntoDB($email,$name,$bday,$klasse);
	echo  json_encode($output);
    }
	
	die;
	}
    
    /**
     * Sorts array by the state if a teacher has slots available or not (/w slots first then without slots
     *
     * @param $teachers
     *
     * @return array
     */
    public function sortByAppointment(&$teachers) {
        
        /** @var Guardian $guardian */
        $guardian = self::$user;
        
        $noSlot = array();
        $withSlot = array();
        
        foreach ($teachers as $data) {
            /** @var Teacher $teacher */
            $teacher = $data['teacher'];
            $avSlots = $teacher->getAllBookableSlots($guardian->getParentId());
            $amountSlots = count($avSlots);
            
            if ($amountSlots == 0)
                array_push($noSlot, $data);
            else
                array_push($withSlot, $data);
            
        }
        
        return $teachers = array_merge($withSlot, $noSlot);
        
        
    }
    
    
    public function handleStudentEditData() {
        
        if (!(self::$user instanceof StudentUser)) {
            $this->notify("Nur Schüler können auf diesen Bereich zugreifen!");
            
            return $this->getDashBoardName();
        }
        
        $this->infoToView['user'] = self::getUser();
        
        if (isset($this->input['console']) && isset($this->input['data'])) {
            $courses = $this->input['data']['courses'];
            
            $this->model->updateStudentData(self::getUser()->getId(), $courses);
            
            $this->notify("Ihre Einstellungen wurden erfolgreich aktualisiert!", 4000, true);
            die(json_encode(array("success" => true)));
        }
        
        return "student_editdata";
    }
    
    /**
     * @return string
     */
    public function handleTeacherEditData() {
        
        if (!(self::$user instanceof Teacher)) {
            $this->notify("Nur Lehrer können auf diesen Bereich zugreifen!");
            
            return $this->getDashBoardName();
        }
        
        $input = $this->input;
        $this->infoToView['user'] = self::getUser();
        // $_SESSION['user']['mail'] $_SESSION['user']['pwd']
        
        if (isset($input['console']) && isset($input['data'])) {
            $data = $input['data'];
            
            $vpmail = $data['vpmail'];
            $vpview = $data['vpview'];
            $newsmail = $data['newsmail'];
            $newshtml = $data['newshtml'];
            
            $this->model->updateTeacherData(self::$user->getId(), $vpview, $vpmail, $newsmail, $newshtml);
            
            $this->notify("Ihre Einstellungen wurden erfolgreich aktualisiert!", 4000, true);
            die(json_encode(array("success" => true)));
            
        }
        
        
        $this->infoToView['vpmail'] = self::$user->getVpMailStatus();
        $this->infoToView['vpview'] = self::$user->getVpViewStatus();
        $this->infoToView['newsmail'] = self::$user->getNewsMailStatus();
        $this->infoToView['newshtml'] = self::$user->getNewsHTMLStatus();
        
        return "teacher_editdata";
    }
    
    /**
     * @return string
     */
    public function handleParentEditData() {
        
        if (!(self::$user instanceof Guardian)) {
            $this->notify("Nur Eltern können auf diesen Bereich zugreifen!");
            
            return $this->getDashBoardName();
        }
        
        $input = $this->input;
        $this->infoToView['user'] = self::getUser();
        // $_SESSION['user']['mail'] $_SESSION['user']['pwd']
        
        if (isset($input['console']) && isset($input['data'])) {
            $data = $input['data'];
            $pwd = $data['pwd'];
            $mail = $data['mail'];
            $name = $data['name'];
            $surname = $data['surname'];
            $oldpwd = $data['oldpwd'];
            $getnews = $data['getnews'];
            $htmlnews = $data['htmlnews'];
            //Teacher AND Student handling needs to be worked on
            if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
                die(json_encode(array("success" => false, "notifications" => array("Bitte geben sie eine valide Emailadresse an!"))));
            }
            
            
            if ($oldpwd == "") {
                die(json_encode(array("success" => false, "notifications" => array("Bitte geben sie ihr altes Passwort an!"))));
            } else if (!$this->model->passwordValidate(self::getUser()->getEmail(), $oldpwd)) {
                die(json_encode(array("success" => false, "notifications" => array("Ihr altes Passwort ist nicht korrekt!"), "resetold" => true)));
            }
            
            if ($pwd != "") {
                $this->model->changePwd(self::getUser()->getId(), $pwd);
            }
            
            
            $succ = $this->model->updateUserData(self::getUser()->getId(), $name, $surname, $mail, $getnews, $htmlnews);
            
            if (!$succ) {
                die(json_encode(array("success" => false, "notifications" => array("Die angegebene Emailadresse ist bereits mit einem anderen Account verknüpft!"))));
            }
            /*
            $_SESSION['user']['mail'] = $mail;
            if ($pwd != "")
                $_SESSION['user']['pwd'] = $pwd;
            */
            $this->notify("Ihre Nutzerdaten wurden erfolgreich aktualisiert!", 4000, true);
            die(json_encode(array("success" => true)));
            
        }
        
        
        $this->infoToView['newsmail'] = self::$user->getNewsMailStatus();
        $this->infoToView['newshtml'] = self::$user->getNewsHTMLStatus();
        
        return "parent_editdata";
    }
    
    public final function getValueIfNotExistent($arr, $key, $defVal) {
        return isset($arr[$key]) ? $arr[$key] : $defVal;
    }
    
    public final function getOption($key, $defVal = '') {
        return $this->getValueIfNotExistent($this->model->getOptions(), $key, $defVal);
    }
    
    
    /**
     * get Newsletters to View
     */
    public function getNewsletters() {
        $model = $this->model->getInstance();
        $news = $this->model->getNewsIds();
        $newsletters = array();
        
        foreach ($news as $n) {
            $newsletter = new Newsletter();
            $newsletter->createFromId($n[0]);
            $newsletters[] = $newsletter;
            unset($newsletter);
        }
        $this->infoToView["newsletters"] = $newsletters;
        $this->infoToView["schoolyears"] = $model->getNewsYears();
        
    }
    
	
	 /**
     *
     * send request kids registration key Email
     * @param string email
	 * @param string content
     */
    protected function sendKeyRequestMail($email,$content) {
		require_once("PHPMailer.php");
		//sending emails
        $phpmail = new PHPMailer();
        $phpmail->setFrom("direktion@suso.konstanz.de", "Suso-Intern");
		$phpmail->CharSet = "UTF-8";
		$phpmail->isHTML();
		$phpmail->AddAddress($email);
		$phpmail->Subject = date('d.m.Y - H:i:s') . "Suso-Intern Ihre Registrierungsanfrage";
		$phpmail->Body = $content;
			
		$send = true;
		
		//Senden
		if (!$phpmail->Send()) {
			$send = false;
		} 
		
		return $send;
        }
    
	/**
     * 
     * send mail after user registration
     * @param string email
     * @param string token
     */
    private function sendRegistrationConfirmation($email,$token) {
        require_once("PHPMailer.php");
        //Email Text
        $content = 'Über diese Emailadresse wurde eine Registrierungsanfrage an das interne Benutzersystem des Heinrich-Suso-Gymnasiums 
        gesendet. Ihre Registrierung können Sie mit folgendem Link abschließen: <a href="https://www.suso.schulen.konstanz.de/intern/index.php?type=confirm&tkn='.$token.'">Registrierung abschließen</a>.<br>
        Wenn Sie den link nicht aus Ihrem Email Programm heraus öffnen können, kopieren Sie bitte die folgende Zeile und fügen Sie diese in die 
        Adresszeile Ihres Browsers ein: <br><br>
        https://www.suso.schulen.konstanz.de/intern/index.php?type=confirm&tkn='.$token.
        '<br> Dieser Link ist 24h aktiv!<br><br> Sollten Sie diese Registrierung nicht getätigt haben oder diese versehentlich mit Ihrem Email 
        Konto erfolgt sein, können Sie diese Nachricht ignorieren.<br><br><b>Diese Email wurde automatisch vom System versandt. Bitte antworten Sie 
        nicht auf diese Email.';
    
        
        //sending emails
        $phpmail = new PHPMailer();
        $phpmail->setFrom("noreply@suso.konstanz.de", "Suso-Intern");
		$phpmail->CharSet = "UTF-8";
		$phpmail->isHTML();
		$phpmail->AddAddress($email);
		$phpmail->Subject = date('d.m.Y - H:i:s') . " Suso-Intern Ihre Registrierungsanfrage";
		$phpmail->Body = $content;
			
		$send = true;
		
		//Senden
		if (!$phpmail->Send()) {
			$send = false;
		} 
		
		return $send;
    }


    /**
     * complete registration after user has confirmed registration
     * and send a amil with success info
     */
	private function completeRegistration($email) {
        require_once("PHPMailer.php");
        //Email Text
        $content = 'Ihre Registrierung für die Suso-Intern-Anwendung ist abgeschlossen!<br/><br/>
        Zur Nutzung der Funktionalitäten müssen Sie Ihre Kinder mittels der erhaltenen Registrierungscodes Ihrem Konto zuweisen.<br/>
        Diesen Code haben Sie mit der Anmeldebestätigung erhalten. <br/>Bitte beachten Sie auch die Nutzeranleitung auf der Website des Heinrich-Suso-Gymnasiums. 
        <br/><b>Eltern neuer Sextaner beachten bitte, dass die Kinder erst ab September in das System integriert werden.</b><br/>
        Wenn Sie keinen Registrierungscode erhalten haben, können Sie diesen manuell im System anfordern. Sie erhalten dann eine Email mit dem erforderlichen Code. Dieser Vorgang wird manuell bearbeitet und kann einige Tage dauern!';
                
        //sending emails
        $phpmail = new PHPMailer();
        $phpmail->setFrom("noreply@suso.konstanz.de", "Suso-Intern");
		$phpmail->CharSet = "UTF-8";
		$phpmail->isHTML();
		$phpmail->AddAddress($email);
		$phpmail->Subject = date('d.m.Y - H:i:s') . " Suso-Intern Bestätigung Ihrer Benutzerregistrierung";
		$phpmail->Body = $content;
			
		$send = true;
		
		//Senden
		if (!$phpmail->Send()) {
			$send = false;
		} 
		
		return $send;
    }

    /**
     *
     *triggering email via phpmailer
     *
     * @param array () containing list of mail recipients (User object)
     */
    private function sendMails($list) {
        $currentTime = date('d.m.Y H:i:s');
        $this->model->writeToVpLog("Starting to send mails on " . $currentTime);
        require("PHPMailer.php");
        //sending emails
        $timestamp = time();
        $datum = date("Y-m-d  H:i:s", $timestamp);
        /** @var Teacher $l */
        foreach ($list as $l) {
            /** @var PHPMailer $phpmail */
            $phpmail = new PHPMailer();
            $phpmail->setFrom("stundenplan@suso.konstanz.de", "Vertretungsplan Suso");
            $phpmail->CharSet = "UTF-8";
            $phpmail->isHTML();
            $phpmail->AddAddress($l->getEmail());
            $phpmail->Subject = date('d.m.Y - H:i:s') . " aktueller Vertretungsplan";
            $phpmail->Body = $this->model->makeHTMLVpMailContent($l);
            
            //Protokolldaten vorbereiten
            //Mailadressen der Instanz:
            $allmailstring = "";
            foreach ($phpmail->getAllRecipientAddresses() as $ema) {
                if ($allmailstring == "") {
                    $allmailstring = $ema[0];
                } else {
                    $allmailstring = $allmailstring . ';' . $ema[0];
                }
            }
            $cont = null;
            
            
            //Senden
            if (!$phpmail->Send()) {
                echo "cannot send!";
                //$mail[$x]->Send() liefert FALSE zurück: Es ist ein Fehler aufgetreten
                $currentTime = date('d.m.Y H:i:s');
                $this->model->writeToVpLog("....failure." . $phpmail->ErrorInfo . " Trying to reach " . $l->getEmail() . " " . $currentTime);
            } else {
                echo "mail gesendet an: " . $l->getEmail() . '<br>';
                //Eintrag des Sendeprotokolls
                $currentTime = date('d.m.Y H:i:s');
                $this->model->writeToVpLog($l->getEmail() . " " . $currentTime);
                
                //Inhalt
                //$this->model->writeToVpLog("....success");
            }
            
            
            //Trage email Versanddatum in DB ein
            foreach ($l->getCoverLessonNrs() as $cl) {
                $this->model->UpdateVpMailSentDate($cl);
            }
            
            $allmailstring = null;
            $cont = null;
        }
        $this->model->writeToVpLog("*****************************************************");
    }
    
    public function checkCaptcha($token, $ignoreEmpty = false) {
        
        
        if ($token == null || $token == "" && $ignoreEmpty) {
            ChromePhp::info("Empty Captcha passing through...");
            
            return true;
        }
        
        $resp = $this->model->checkCaptcha($token);
        
        return $resp->success == 1;
    }
    
    
}

?>
