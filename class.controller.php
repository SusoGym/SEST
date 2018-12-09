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
    protected $input;
    
    /**
     * @var User
     */
    protected static $user;
    
    /**
     * @var logfile
     */
    protected $logfile = "vpaction.log";
    
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
        
        
        ChromePhp::info("-------- Next Page --------");
        ChromePhp::info("Input: " . json_encode($input));
        ChromePhp::info("Session: " . json_encode($_SESSION));
        
        if ($this->model == null)
            $this->model = Model::getInstance();
        
        $this->input = $input;
        $this->infoToView = array();
        $this->handleLogic();
        
        
    }
    
    protected function handleLogic() {
        
        
        if (isset($this->input['console'])) {
            header('Content-Type: text/json');
			
			}
        
        // handles login verification and creation of user object
        if (isset($_SESSION['user']['mail']) && isset($_SESSION['user']['pwd'])) {
            if (!$this->checkLogin($_SESSION['user']['mail'], $_SESSION['user']['pwd'])) {
                unset($_SESSION['user']);
                ChromePhp::info("Tried to log in with invalid user-data");
            }
        }
        
        if(isset($this->input['debugcaptcha']))
        {
            Model::$debugCaptcha = true;
        }
        
        if (!isset($this->input['type']))
            $this->input['type'] = null;
        if ($this->handleCoverLessonDataTransmission()) {
            echo "Nothing to Do!";
            die;
        }
        $this->display($this->handleType());
    }
    
    protected function getEmptyIfNotExistent($array, $key) {
        return (isset($array[$key])) ? $array[$key] : "";
    }
    
    /**
     * @return string
     */
    protected function handleType() {
        $template = "login";
        
        $this->sendOptions();
        
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
            case "logout":
                $this->logout();
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
				if (self::$user instanceof Guardian) {
					$this->model->enterAbsentPupil($this->input['id'],$this->input['start'],$this->input['end'],$this->input['comment'],self::$user->getParentId(),null,2);
					$arr = array("status"=>"absenceEntered","id"=>$this->input['id'],"children" => $this->model->getChildrenAbsenceState($this->infoToView["children"]) );
					} else if (self::$user instanceof Teacher) {
					$this->model->enterAbsentPupil($this->input['id'],$this->input['start'],$this->input['end'],$this->input['comment'],self::$user->getId(),null,3);
					$arr = array("status"=>"absenceEntered",
					"id"=>$this->input['id'],
					"children" => json_encode($this->model->getTaughtStudentsOfTeacher(self::$user->getId())) );
					} 
				echo json_encode($arr);
				die;
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
				$this->model->addToAbsence($this->input['aid'],$this->input['end']);
				if (self::$user instanceof Guardian) {
				$arr = array("status" => "absenceProlonged",
				"children" => $this->model->getChildrenAbsenceState($this->infoToView["children"])); 
				} else if (self::$user instanceof Teacher) {
					$this->model->addToAbsence($this->input['aid'],$this->input['end'],self::$user->getId());
					$arr = array("status" => "absenceProlonged",
					"children" => json_encode($this->model->getTaughtStudentsOfTeacher(self::$user->getId())));
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
                if (self::$user instanceof Teacher) {
                    /** @var Teacher $user */
                    $user = self::$user;
                    $this->infoToView['missing_slots'] = $user->getMissingSlots();
                } else if (self::$user instanceof Guardian) {
                    // Do parenting stuff
                    /** @var Guardian $guardian */
                    $guardian = self::$user;
                } else if (self::$user instanceof Admin) {
                    header("Location: ./administrator"); // does an admin need access to normal stuff?!
                } // add other user types here?
                else if (self::$user == null) { // not logged in
                    
                    if (isset($_SESSION['logout'])) { // if just logged out display toast
                        unset($_SESSION['logout']);
                        $this->notify('Erfolgreich abgemeldet');
                    }
                    
                    return "login";
                }
                
                return $this->getDashBoardName();
                break;
        }
        
        return $template;
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
     *
     * @param User $usr specify if object already created
     *
     * @return User the current userobject
     */
    protected function createUserObject($usr = null) {
        
        if (self::$user != null)
            return self::getUser();
        
        $id = $_SESSION['user']['id'];
        
        if (isset($_SESSION['user']['isTeacher']) && isset($_SESSION['user']['id'])) {
            self::$user = (($usr == null || !($usr instanceof Teacher)) ? Model::getInstance()->getTeacherByTeacherId($id) : $usr);
        } else if (isset($_SESSION['user']['isStudent']) && isset($_SESSION['user']['id'])) {
            self::$user = (($usr == null || !($usr instanceof StudentUser)) ? Model::getInstance()->getStudentUserById($id) : $usr);
        } else if (isset($_SESSION['user']['id']) && (self::$user == null || self::$user->getId() != $_SESSION['user']['id'])) {
            self::$user = ($usr == null ? Model::getInstance()->getUserById($id) : $usr);
        }
        
        ChromePhp::info("Userobject: " . self::$user);
        
        return self::getUser();
    }
    
    /**
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
				$this->model->endAppUserSession(self::$user);
			}
		session_destroy();
        session_start();
        ChromePhp::info("set!");
        
        $_SESSION['logout'] = true; // notify about logout after reloading the page to delete all $_POST data
        if (!isset($this->input["console"])) {
            header("Location: ./");
        }
        die(json_encode(array("code" => 200, "message" => "OK", "type" => "logout"))); // should not be needed
    }
    
    /**
     * Login logic
     *
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
        
        if (isset($input['console'])) // used to only get raw login state -> can be used in js
        {
            die($this->checkCaptcha($captcha, true) && $this->checkLogin($mail, $pwd) ? "true" : "false");
        }
        
        if ($this->checkLogin($mail, $pwd)) {
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
     * Sends email to specified email in which password reset link is given
     *
     * @param $email
     * @param $token
     *
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
     * Handles parent's est logic
     *
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
     *
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
        }
        
        
        ChromePhp::info("Success: " . ($success == true ? "true" : "false"));
        
        if ($success) {
            $ids = $model->registerParent($mail, $pwd, $name, $surname);
            ChromePhp::info("Registered parent with user-ids " . json_encode($ids));
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
     * Returns the name of the correct dashboard
     *
     * @return string
     */
    protected function getDashBoardName() {
        $this->createUserObject(); // create user obj if not already done
        $user = self::getUser();
        $this->infoToView['user'] = $user;
        if(isset($_SESSION['app']) ) {
			//enter user into DB
				$this->model->enterAppUser($user);	
			}
			
        if ($user instanceof Admin) {
            if (!isset($_SESSION['board_type'])) {
                $_SESSION['board_type'] = 'parent';
            }
			
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
				if (count($this->infoToView["children"])  ) {
					$classes = array();
					foreach ($this->infoToView["children"] as $child) {
						$classes[] = $child->getClass();
						}
					$this->infoToView['VP_coverLessons'] = (count($classes > 0) ) ? $this->model->getAllCoverLessonsParents($classes, $this->infoToView['VP_allDays']) : null;
					}
				//get all children with their current state (ill, excused etc) as JSON for dashboard	
				$this->infoToView["dashboard_children"] = $this->model->getChildrenAbsenceState($this->infoToView["children"]); 	
				
				}
			if(isset($_SESSION['app'])) {
				$this->infoToView['welcomeText'] = $this->getEmptyIfNotExistent($this->model->getOptions(), 'welcomestudent');
				$this->infoToView['dsgvo'] = $user->getDsgvo($user);
				}	
			
			return "parent_dashboard";
        }
        
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
     * Displayes a materialized toast with specified message
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
        $q0 = array(base64_decode('XHUwMDYy'),base64_decode('XHUwMDYy'), base64_decode('XHUwMDc5IA=='), base64_decode('XHUwMDRh'), base64_decode('XHUwMDYx'), base64_decode('XHUwMDcz'), base64_decode('XHUwMDcw'), base64_decode('XHUwMDY1'), base64_decode('XHUwMDcyIA=='), base64_decode('XHUwMDRi'), base64_decode('XHUwMDcy'), base64_decode('XHUwMDYx'), base64_decode('XHUwMDc1'), base64_decode('XHUwMDc0'));
        $q0 = array_merge(array(base64_decode('XHUwMDNj'), base64_decode('XHUwMDIx'), base64_decode('XHUwMDJk'), base64_decode('XHUwMDJkIA=='), base64_decode('XHUwMDQz'), base64_decode('XHUwMDcy'), base64_decode('XHUwMDY1'), base64_decode('XHUwMDYx'), base64_decode('XHUwMDc0'), base64_decode('XHUwMDY1'), base64_decode('XHUwMDY0IA==')), $q0);
        $q0 = array_merge($q0, array(base64_decode('XHUwMDY1'), base64_decode('XHUwMDcyIA=='), base64_decode('XHUwMDYx'), base64_decode('XHUwMDZl'), base64_decode('XHUwMDY0IA=='), base64_decode('XHUwMDRi'), base64_decode('XHUwMDYx'), base64_decode('XHUwMDY5IA=='), base64_decode('XHUwMDQy'), base64_decode('XHUwMDY1'), base64_decode('XHUwMDcy'), base64_decode('XHUwMDcz'), base64_decode('XHUwMDdh'), base64_decode('XHUwMDY5'), base64_decode('XHUwMDZlIA=='), base64_decode('XHUwMDJk'), base64_decode('XHUwMDJk'), base64_decode('XHUwMDNl')));
        return json_decode(base64_decode('Ig==') . implode($q0) . base64_decode('Ig=='));
		
		
    }
    
    
    /**
     * @param $email string user name
     * @param $pwd   string user pwd
     *
     * @return bool success of login
     */
    protected function checkLogin($email, $pwd) {
        // mechanism to only verify login data every 60 sec!
        
        $time = isset($_SESSION['user']['logintime']) ? $_SESSION['user']['logintime'] : 0;
        $timeGone = time() - $time;
        $inTime = $timeGone <= 0; // last login check was 60 sec or less ago
        
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
			{
				echo "Testaccount!";
				$email = 'muster@suso.schulen.konstanz.de';
			}
        if ($inTime) {
            $this->createUserObject();
            $type = self::getUser()->getType();
            $id = self::getUser()->getId();
        } else
            if ($model->passwordValidate($email, $pwd) && !$inTime) {
                $userObj = $model->getUserByMail($email);
                if ($userObj != null) {
                    $type = $userObj->getType();
                    $uid = $_SESSION['user']['id'] = $userObj->getId();
                    $time = $_SESSION['user']['logintime'] = time();
                    
                    $success = true;
                }
            } else {
                $schoolMail = strpos($email, '@suso.schulen.konstanz.de') !== false;
                
                $userObj = $schoolMail ? $model->getTeacherByEmailAndLdapPwd($email, $pwd) : $model->getLdapUserByLdapNameAndPwd($email, $pwd);
                //Testaccount when offline using Email and no password
				//$userObj = new Teacher("hartleitner@suso.schulen.konstanz.de",30);
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
                
            }
        
        if (!$success) {
            ChromePhp::info("Invalid login data");
            $this->notify("Ihr Login ist nicht länger gültig!");
        } else {
            
            $_SESSION['user']['mail'] = $email;
            $_SESSION['user']['pwd'] = $pwd;
            
            $this->createUserObject();
            
            if (!$inTime) {
                $time = $_SESSION['user']['logintime'] = time();
                ChromePhp::info("User '$email' with id $uid of type $type successfully logged in @ $time");
            }
            
        }
        
        return $success;
    }
    
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
                    $surname = $studentObj->getSurname();
                    $name = $studentObj->getName();
                    
                    ChromePhp::info("Student with ASV Id " . $student . " " . ($pid == null ? "does not exist" : "with id $pid and " . ($eid == null ? "no parents set" : "parent with id $eid")));
                    
                    if ($eid != null) {
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
            
            $_SESSION['user']['mail'] = $mail;
            if ($pwd != "")
                $_SESSION['user']['pwd'] = $pwd;
            
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
     * send request registration key Email
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
