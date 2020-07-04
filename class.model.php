<?php

/**
 * The original model class
 */
class Model {
    /**
     * @var Connection
     */
    protected static $connection;
    /**
     * @var Model
     */
    protected static $model;
    
    public static $debugCaptcha = false;
    
    /**
     * @var monate
     */
    private $monate = null;//Array("mnum"=>string,"mstring"=>string,"jahr"=>int) der Monate mit Terminen
    
    
    /**
     *Konstruktor
     */
    protected function __construct() {
        if (self::$connection == null)
            self::$connection = new Connection();
        
    }
    
    static function getInstance() {
	     return self::$model == null ? self::$model = new Model() : self::$model;
    }
    
    /**
     *getOptions
     *returns option from DB table options
     *e.g. slot assignment, booking period, allowed bookings etc
     *
     * @return array()
     */
    public function getOptions() {
        $options = array();
        $data = self::$connection->SelectAssociativeValues("SELECT * FROM options");
        
        foreach ($data as $d) {
            $options[$d['type']] = $d['value'];
            
        }
        
        return $options;
    }
    
    
    /**
     * get values from ini-file
     *
     * @return array
     */
    public function getIniParams() {
        return self::$connection->getIniParams();
    }
    
    
    /**
     * @param string $vorname Schueler Vorname
     * @param string $name    Schueler Nachname
     *
     * @return Student
     **/
    public function getStudentByName($name, $surname = null, $bday = null) {
        
        $name = self::$connection->escape_string($name);
        if ($surname != null) {
            $surname = self::$connection->escape_string($surname);
            $wholeName = str_replace(' ', '', $name . $surname);
        } else {
            $wholeName = $name;
        }
        
        $data = self::$connection->selectAssociativeValues("SELECT * FROM schueler WHERE Replace(CONCAT(vorname, name), ' ', '') = '$wholeName'   AND gebdatum = '$bday'");
        
        if ($data == null)
            return null;
        
        $data = $data[0];
        
        return new Student($data['id'], $data['klasse'], $data['name'], $data['vorname'], $data['gebdatum'], $data['eid'], $data['eid2']);
    }
	
	/**
	* @param string (the ID provided from external source, i.e. school administration software
	* @return student
	*/
	public function getStudentByASVId($asvId) {
		$data = self::$connection->selectAssociativeValues('SELECT * FROM schueler WHERE ASV_ID = "'.$asvId.'"');
        if ($data == null)
            return null;
        
        $data = $data[0];
        return new Student($data['id'], $data['klasse'], $data['name'], $data['vorname'], $data['gebdatum'], $data['eid'], $data['eid2']);
    	
	}
	
	/**
	* get student's ASV ID
	* @param int id
	* @return string ASVId
	*/
	public function getASVId($id){
	$data = self::$connection->selectValues('SELECT ASV_ID FROM schueler WHERE id = '.$id);	
	if ($data == null)
            return null;
     else   
		return $data[0][0];
	}
    
    /**
     * @param $uid int
     *
     * @return User | Teacher | Admin | Guardian
     */
    public function getUserById($uid,$data = null) {
        
        if ($data == null)
            $data = self::$connection->selectAssociativeValues("SELECT * FROM user WHERE id=$uid");
        if ($data == null)
            return null;
        if (isset($data[0]))
            $data = $data[0];

        $type = $data['user_type'];
        //Debug::writeDebugLog(__method__,"User ".$uid." Type: ".$type);
        switch ($type) {
            case 0: // Admin
                return new Admin($data['id'], $data['email']);
                break;
            case 1: // Parent / Guardian
			    $data2 = self::$connection->selectAssociativeValues("SELECT * FROM eltern WHERE userid=$uid")[0];
                //Debug::writeDebugLog(__method__,"Create Guardian");
                return new Guardian($data['id'], $data['email'], $data2['id'], $data2['name'], $data2['vorname']);
            default:
                return null;
                break;
        }
        
    }
    
    /**
     * @param string $email the user email
     *
     * @return User user
     */
    public function getUserByMail($email) {
        $email = self::$connection->escape_string($email);
        $data = self::$connection->selectAssociativeValues("SELECT * FROM user WHERE email = '$email'");
        if (empty($data))
            return null;
        else 
			return $this->getUserById($data[0]['id'], $data);
    }
	
	
	/**
	* get user's dsgvo status
	* @param int id
	* @return string date of acceptance
	*/
	public function getDsgvoStatus($user) {
	if ($user instanceof Guardian) {
			$table = "eltern";
			$id = $user->getParentId();
	}
	if ($user instanceof Teacher) {
			$table = "lehrer";
			$id = $user->getId();
	}
	if ($user instanceof StudentUser) {
			$table = "schueler";
			$id = $user->getId();
	}
	$dsgvoStatus = null;
	//When admin is logged in, there won't be an ID for Guardian object etc.
	if (isset($id)) {
		$data = self::$connection->selectValues("SELECT dsgvo FROM $table WHERE id = $id");
		if ($data) {
				$dsgvoStatus = $data[0][0];
		}
	}	
	
	
	return $dsgvoStatus;
	}
	
	/*
	**accept dsgvo and enter in DB
	* @param user
	*/
	public function acceptDsgvo($user) {
		$table = "";
		$id = $user->getId();
		if ($user instanceof Guardian) {
			$table = "eltern";
			$id = $user->getParentId();
		}
		if ($user instanceof Teacher) {
				$table = "lehrer";
				$id = $user->getId();
		}
		if ($user instanceof StudentUser) {
				$table = "schueler";
				$id = $user->getId();
		}
		$data = self::$connection->straightQuery("UPDATE $table set dsgvo = CURRENT_TIMESTAMP WHERE id = $id");
	}
    
    /**
     * @param int $tchrId
     *
     * @return array[String => String]
     */
    public function getTeacherNameByTeacherId($teacherId, $data = null) {
        if ($data == null)
            $data = self::$connection->selectAssociativeValues("SELECT * FROM lehrer WHERE lehrer.id=$teacherId");
        
        if (isset($data[0]))
            $data = $data[0];
        
        $surname = isset($data["name"]) ? $data["name"] : null;
        $name = isset($data["vorname"]) ? $data["vorname"] : null;
        $shortName = isset($data["kuerzel"]) ? $data["kuerzel"] : null;
        return array("name" => $name, "surname" => $surname, 'krzl' => $shortName);
    }
    
    
    /**
     * @param int $usrId UserId
     *
     * @return array[Student] array[childrenId]
     */
    public function getChildrenByParentUserId($usrId, $limit = null) {
        if (isset($limit)) {
            $query = "SELECT schueler.* FROM schueler, eltern WHERE 
            ( schueler.eid=eltern.id OR  schueler.eid2 = eltern.id )
            AND eltern.userid=$usrId AND schueler.klasse < $limit"; //a bit crude, isn't it
        } else {
            $query = "SELECT schueler.* FROM schueler, eltern WHERE 
            ( schueler.eid=eltern.id OR  schueler.eid2 = eltern.id )
            AND eltern.userid=$usrId";
        }
        
        $data = self::$connection->selectAssociativeValues($query);
        
        if ($data == null)
            return array();
        
        $students = array();
        
        foreach ($data as $item) {
            $pid = intval($item['id']);
            $student = $this->getStudentById($pid);
            array_push($students, $student);
        }
        
        return $students;
    }
    
    /**
     * get a student by its id
     * @param $studentId int
     *
     * @return Student
     */
    public function getStudentById($studentId) {
        $data = self::$connection->selectAssociativeValues("SELECT * FROM schueler WHERE id=$studentId");
        
        
        if ($data == null)
            return null;
        
        $data = $data[0];
        
        return new Student($data['id'], $data['klasse'], $data['name'], $data['vorname'], $data['gebdatum'], $data['eid'], $data['eid2']);
    }

    /**
     * get a students data as JSON 
     * @param int id
     * @return JSON
     */
    public function getStudentDataJSON($id){
        $student = $this->getStudentById($id);
        $eid = $student->getEid();
        $eid2 = $student->getEid2();
        //get data if locker is hired
        $locker = null;
        $lckrQueryData = self::$connection->selectValues("SELECT id,nr,location,hiredate FROM lockers WHERE hired = " . $id);
				if ( !empty($lckrQueryData) ) {
					$locker = array("id" => $lckrQueryData[0][0],"nr" => $lckrQueryData[0][1], "location" => $lckrQueryData[0][2] , "hiredate" => $lckrQueryData[0][3] );
                    } 
        //get data if library books are hired
        $libraryData = $this->getSkolibData($student->getASVId());
        $studentData = array("id"=>$id, 
        "asvid"=>$student->getASVId(),
        "klasse" => $student->getClass(),
        "name"=> $student->getFullName(),
        "bday"=>$student->getBday(),
        "parent1"=> ($eid != null) ? $this->getParentUserByParentId($eid ) : null ,
        "parent2"=> ($eid2 != null) ? $this->getParentUserByParentId($eid2 ) : null,
        "locker" => $locker,
        "library" => $libraryData
        ) ;
        
        return json_encode($studentData);
    }

    /**
     * get library data of a student
     * used when deregistering a student to ensure that no borrowed books are due
     * uses the skolib api
     * connecting with curl
     * @param string asvid
     * @return json string
     */
    public function getSkolibData($asvid) {
        $apiData = self::$connection->selectValues('SELECT api_url,token,api_action FROM api_token WHERE customer = "skolib_library_check"'); 
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiData[0][0]);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_ENCODING, 'identity');
        curl_setopt($ch, CURLOPT_POST, 1); // set POST method
        //token is hard coded
        curl_setopt($ch, CURLOPT_POSTFIELDS,'tkn='.$apiData[0][1].'&type='.$apiData[0][2].'&c='.$asvid);
        
        $result = utf8_encode(curl_exec($ch));
                if (curl_errno($ch)) {
                    throw new Exception(curl_error($ch));
                }
                
                if ($result == false) {
                    throw new Exception("Response was empty!");
                }
        curl_close($ch);

        
        //remove strange symbols at the beginning and keep it a json string  -- UTF-8 BOM (should not be sent by the skolib API!!)
        if ( strpos($result,"[") == true ) {
        $jsonString = "[" . explode("[",$result)[1] ;
        } else {
            $jsonString = null;
        }
        return $jsonString;
        
        }

    /**
     * get all teachers of a class
     * @param string $class
     *
     * @return array[Teacher] array with teacherIds
     */
    public function getTeachersByClass($class) {
        $class = self::$connection->escape_string($class);
        $data = self::$connection->selectValues("SELECT DISTINCT lehrer.id FROM lehrer, unterricht WHERE unterricht.klasse='$class' AND unterricht.lid=lehrer.id"); // returns data[n][data]
        
        if ($data == null)
            return null;
        
        $ids = array();
        foreach ($data as $item) {
            $tid = intval($item[0]);
            array_push($ids, $this->getTeacherByTeacherId($tid));
        }
        
        return $ids;
        
    }
	
	/**
	* get Teacher Id by short name
	* @param string 
	* @return int
	*/
	public function getTeacherIdByShortName($short) {
	$teacher = null;
	$data = self::$connection->selectValues('SELECT id from lehrer WHERE kuerzel="'.$short.'"');
		if (!empty($data) ) {
		$teacher = $data[0][0];
		return $teacher;	
		} else {
		return false;
		}
	
	}
    
    /**
     * Returns all Teachers
     *
     * @return array[Teacher]
     */
    public function getTeachers() {
        $data = self::$connection->selectAssociativeValues("SELECT * FROM lehrer ORDER BY name,vorname"); // returns data[n][data]
        
        $teachers = array();
        foreach ($data as $item) {
            $tid = intval($item['id']);
            array_push($teachers, $this->getTeacherByTeacherId($tid, $item));
        }
        
        return $teachers;
    }
    
    /**
     * get a teachwer by its it
     * @param $tchrId int teacherId
     *
     * @return Teacher
     */
    public function getTeacherByTeacherId($tchrId, $data = null) {
        if ($data == null)
            $data = self::$connection->selectAssociativeValues("SELECT * FROM lehrer WHERE id='$tchrId'");
        
        if (isset($data[0]))
            $data = $data[0];
        
        
        if ($data == null)
            return null;
        
        return new Teacher($data['email'], $data['id'], $data);
    }
    
    /**
     * get teachers LDAPName
     * @param $teacherId
     * @param $rawData
     *
     * @return string
     */
    public function getTeacherLdapNameByTeacherId($teacherId, $rawData = null) {
        if ($rawData == null)
            $rawData = self::$connection->selectAssociativeValues("SELECT ldapname FROM lehrer WHERE id='$teacherId'");
        
        if ($rawData == null)
            return null; // empty / not found
        
        if (isset($rawData[0]))
            $rawData = $rawData[0];
        
        return $rawData;
    }
    
    /**
     * get teacher's UntisName
     * @param $teacherId
     * @param $rawData
     *
     * @return string
     */
    public function getTeacherUntisNameByTeacherId($teacherId, $rawData = null) {
        $returnData = null;
        if ($rawData == null) {
            $data = self::$connection->selectValues("SELECT untisname FROM lehrer WHERE id='$teacherId'");
            if ($data == null) {
                $returnData = null; // empty / not found
            } else {
                $returnData = $data[0][0];
            }
        }
        if (isset($rawData["untisName"])) {
            $returnData = $rawData["untisName"];
        }
        
        return $returnData;
    }
    
    /**
     * get teacher's short name
     * @param $teacherId
     * @param $rawData
     *
     * @return string
     */
    public function getTeacherShortNameByTeacherId($teacherId, $rawData = null) {
        $returnData = null;
        if (!isset($rawData["shortName"])) {
            $data = self::$connection->selectValues("SELECT kuerzel FROM lehrer WHERE id='$teacherId'");
            if ($data == null) {
                $returnData = null; // empty / not found
            } else {
                $returnData = $data[0][0];
            }
        }
        if (isset($rawData["shortName"]))
            $returnData = $rawData["shortName"];
        
        return $returnData;
    }
    
    /**
     * get a teacher's amount of lessons taught
     * @param $teacherId int
     *
     * @return int
     */
    public function getTeacherLessonAmountByTeacherId($teacherId) {
        $data = self::$connection->selectValues("SELECT deputat FROM lehrer WHERE id='$teacherId'");
        
        $lessons = $data[0][0];
        
        return $lessons;
    }
    
    /**
     * get a teacher by email and pwd
     * @param $email
     * @param $pwd
     *
     * @return Teacher | null
     */
    public function getTeacherByEmailAndLdapPwd($email, $pwd) {
        
        $email = self::$connection->escape_string($email);
        
        $data = self::$connection->selectAssociativeValues("SELECT * FROM lehrer WHERE email='$email'");
        
        if (isset($data[0]))
            $data = $data[0];
        
        if ($data == null)
            return null;
        
        $tId = $data['id'];
        $ldapName = $this->getTeacherLdapNameByTeacherId($tId, $data);
        
        if ($ldapName == null)
            die("LDAP name not set for $email! If you are 1000% sure this is your real suso email, please contact you system admin of choice."); // rip
        
        return $this->getLdapUserByLdapNameAndPwd($ldapName, $pwd);
    }
    
    /**
	* studentUser
	* used to get courses list
	* @param int id
	* @return StudentUser object
	*/
    public function getStudentUserById($id) {
        $data = self::$connection->selectAssociativeValues("SELECT * FROM schueler WHERE id='$id'");
        
        if (!isset($data[0])) {
            return null;
        }
        $data = $data[0];
        
        return new StudentUser($data['id'], $data['name'], $data['vorname'], $data['klasse'], $data['gebdatum'], $data['eid'],$data['eid2'], $data['kurse']);
        
    }
	
	
    
    /**
     * @param $ldapName
     * @param $pwd
     * @param $data
     *
     * @return null|Teacher | StudentUser
     */
    public function getLdapUserByLdapNameAndPwd($ldapName, $pwd, $data = null) {
        
        $ldapName = self::$connection->escape_string($ldapName);
        
        if ($data == null) {
            
            $novelData = $this->checkNovellLogin($ldapName, $pwd);
            //Debug::writeDebugLog(__method__,$ldapName." -- ".$pwd." : ".$novelData);
            if (!isset($novelData->{'code'}) || !isset($novelData->{'type'}) || $novelData->{'code'} != "200" || $novelData->{'type'} != 'Teacher') {
                ChromePhp::info(json_encode($novelData));
                if (isset($novelData->{'type'}) && $novelData->{'type'} == "student" && $novelData->{'code'} == "200") {
                    
                    $surname = self::$connection->escape_string($novelData->{'surname'});
                    $givenName = self::$connection->escape_string($novelData->{'givenname'});
                    
                    $query = "SELECT * FROM schueler WHERE klasse='" . $novelData->{'class'} . "' AND NAME LIKE '%$surname%' AND (";
                    $names = explode(' ', $givenName);
                    
                    for ($i = 0; $i < sizeof($names); $i++) {
                        if ($i != 0)
                            $query .= " OR";
                        $query .= " vorname LIKE '%" . $names[$i] . "%'";
                    }
                    $query .= ")";
                    $data = self::$connection->selectAssociativeValues($query);
                    
                    if (!isset($data[0])) {
                        ChromePhp::error("LDAP ist valide, MySQL jedoch nicht. Bitte wende dich an einen Systemadministrator. \n" . json_encode(array("query" => $query, "data" => $data, "names" => $names), JSON_PRETTY_PRINT));
                        die("LDAP ist valide, MySQL jedoch nicht. Bitte wende dich an einen Systemadministrator.");
                    }
                    $data = $data[0];
                    
                    return new StudentUser($data['id'], $data['name'], $data['vorname'], $data['klasse'], $data['gebdatum'], $data['eid'],$data['eid2'], $data['kurse']);
                    
                } else {
                    return null;
                }
            }
            
            $data = self::$connection->selectAssociativeValues("SELECT * FROM lehrer WHERE ldapname='$ldapName'");
        }
        
        
        if (isset($data[0]))
            $data = $data[0];
        
        if ($data == null)
            return null;
        
        $tId = $data['id'];
        $email = $data['email'];
        
        return new Teacher($email, $tId);
    }
    
    /**
     *returns if slot already assigned - reloading
     *
     * @param int slotId
     * @param int teacherId
     *
     * @return bool
     */
    private function checkAssignedSlot($slotId, $teacherId) {
        $data = self::$connection->selectvalues("SELECT slotid FROM bookable_slot WHERE slotid='$slotId' AND lid='$teacherId'");
        if (isset($data)) {
            return true;
        } else {
            return false;
        }
        
    }
    
    /**
     *get existing slots for parent-teacher meeting
     *
     * @return array(array("id","start","ende"))
     */
    public function getSlots() {
        $slots = array();
        $data = $tchrs = self::$connection->selectValues("SELECT id,anfang,ende FROM time_slot ORDER BY anfang ");
        if (isset($data)) {
            foreach ($data as $d) {
                $slots[] = array("id" => $d[0], "anfang" => $d[1], "ende" => $d[2]);
            }
        }
        
        return $slots;
    }
    
    /**
     *enters a bookable Teacher Slot into DB
     *
     * @param int slotId
     * @param int teacherId
     */
    public function setAssignedSlot($slot, $teacherId) {
        if (!$this->checkAssignedSlot($slot, $teacherId)) {
            self::$connection->straightQuery("INSERT INTO bookable_slot (`slotid`,`lid`) VALUES ('$slot','$teacherId')");
        }
    }
    
    /**
     *deletes an assigned Slot from DB
     *
     * @param slotId
     * @param teacherId
     */
    public function deleteAssignedSlot($slotId, $teacherId) {
        self::$connection->straightQuery("DELETE FROM bookable_slot WHERE slotid='$slotId' AND lid='$teacherId'");
    }
    
    
    /**
     *returns assigned slots of a teacher
     *
     * @param int teacherId
     *
     * @returns array(int)
     */
    public function getAssignedSlots($teacher) {
        $slots = array();
        $data = self::$connection->selectValues("SELECT slotid FROM bookable_slot WHERE lid='$teacher'");
        if (isset($data)) {
            foreach ($data as $d) {
                $slots[] = $d[0];
            }
        }
        
        return $slots;
    }
    
    
    /**
     * @param $eid int parentId
     * @return Guardian
     */
    public function getParentByParentId($eid) {
        $data = self::$connection->selectAssociativeValues("SELECT userid FROM eltern WHERE id='$eid'");
        if ($data == null)
            return null;
        $data = $data[0];
        
        return $this->getUserById($data['userid']);
    }
	
	/**
	* getParentUserByParentId - not sure if above function is working properly
	* @param int eid
	* @return array
	*/
	public function getParentUserByParentId($eid){
	$data = self::$connection->selectAssociativeValues("SELECT name, vorname, user.email AS email, user.id AS uid 
	FROM eltern, user 
	WHERE eltern.id=$eid
	AND user.id = eltern.userid");
        if ($data == null)
            return null;
        $data = $data[0];
        
        //return new Guardian($data['uid'],$data['email'],$eid,$data['name'],$data['vorname']);
		return array("fullname"=>$data['name'].", ".$data['vorname'],"email"=>$data['email'] );
	}
	/**
	* getParentByEmailAdress
	* @param String email
	* @return Guardian
	*/
	public function getUserByEmail($email){
	$data = self::$connection->selectAssociativeValues("SELECT id FROM user WHERE email='$email'");
        if ($data == null)
            return null;
        $data = $data[0];
        return $this->getUserById($data['id']);
    }	
	
    
    /**
     * add a parent - teacher appointment
     * @param int $slotId
     * @param int $userId
     * @param int $teacherId
     *
     * @return int appointmentId
     */
    public function bookingAdd($slotId, $userId) {
        return self::$connection->insertValues("UPDATE bookable_slot SET eid='$userId' WHERE id='$slotId'");
    }
    
    /**
     * delete a teacher-parent appointment
     * @param int $appointment
     */
    public function bookingDelete($appointment) {
        self::$connection->straightQuery("UPDATE bookable_slot SET eid=NULL WHERE id='$appointment'");
    }
    
    /**
     * @param $parentId    int
     * @param $appointment int
     *
     * @return boolean
     */
    public function parentOwnsAppointment($parentId, $appointment) {
        $data = self::$connection->selectAssociativeValues("SELECT * FROM bookable_slot WHERE id='$appointment'");
        if (isset($data[0]))
            $data = $data[0];
        if (!isset($data) || $data['eid'] == null)
            return true; //throw exception?
        
        return $data['eid'] == $parentId;
    }
    
    /**
     * @param $slotId int
     * @param $userId int
     *
     * @return int appointmentId
     */
    public function getAppointment($slotId, $userId) {
        return -1;
    }
    
    /**
     */
    
    /**
     * returns all bookable or booked slots of a teacher for a parent
     *
     * @param teacherId
     *
     * @return array
     */
    public function getAllBookableSlotsForParent($teacherId, $parentId) {
        $slots = array();
        $data = self::$connection->selectValues("SELECT bookable_slot.id,anfang,ende,eid,time_slot.id FROM bookable_slot,time_slot 
			WHERE lid='$teacherId'
			AND bookable_slot.slotid=time_slot.id
			AND (eid IS NULL OR eid='$parentId')
			ORDER BY anfang");
        if (isset($data)) {
            foreach ($data as $d) {
                $slots[] = array("bookingId" => $d[0], "anfang" => $d[1], "ende" => $d[2], "eid" => $d[3], "slotId" => $d[4]);
            }
        }
        
        return $slots;
    }
    
    /**
     *returns appointments of parent
     *
     * @param int parentId
     *
     * @return array(slotId, bookingId, teacherId)
     */
    public function getAppointmentsOfParent($parentId) {
        $appointments = array();
		//make sure that appointments in total are divided between two parents
		//i.e. second registered parent must be identified 
		$parents = $this->identifySecondParent($parentId);
		if (isset($parents[0]) && isset($parents[1]) ) {
		$parentQuery = '(bookable_slot.eid='.$parents[0].' OR bookable_slot.eid='.$parents[1].')';
		} else {
		if (isset($parents[0])) {
			$parentQuery = 'bookable_slot.eid='.$parents[0];
			}elseif (isset($parents[1])) {
			$parentQuery = 'bookable_slot.eid='.$parents[1];
			}
		}
        $data = self::$connection->selectValues('SELECT time_slot.id,bookable_slot.id,bookable_slot.lid FROM time_slot,bookable_slot
			WHERE time_slot.id=bookable_slot.slotid
			AND '.$parentQuery.' ORDER BY anfang');
		
        if (isset($data)) {
            foreach ($data as $d) {
                $appointments[] = array("slotId" => $d[0], "bookingId" => $d[1], "teacherId" => $d[2]);
            }
        }
        
        return $appointments;
    }

	/**
	* identify second parent of a child
	* @param int eid
	* @return array(int)
	*/
	public function identifySecondParent($parentId) {
	$parents = array();
	$data = self::$connection->selectValues('SELECT DISTINCT eid,eid2 from schueler WHERE eid='.$parentId.' OR eid2 = '.$parentId);
	if (!empty($data) ) {
	$dataset = $data[0];
	if (isset($dataset[1]) ) {
		//2 parents registered
		array_push($parents ,$dataset[0]);
		array_push($parents ,$dataset[1]);
		} else {
		//1 parent registered
		array_push($parents ,$dataset[0]);
		}
	}
	
	return $parents;
	}

    
    /**
     * returns taught classes of teacher
     *
     * @param int teacherId
     *
     * @return array(string)
     */
    public function getTaughtClasses($teacherId) {
        $data = self::$connection->selectValues("SELECT klasse FROM unterricht WHERE lid='$teacherId' ORDER BY klasse");
        $classes = array();
        if (isset($data)) {
            foreach ($data as $d) {
                $classes[] = $d[0];
            }
        }
        
        return $classes;
    }
    
    /**
     *returns appointments of teacher
     *
     * @param int teacherId
     *
     * @return array(slotId, bookingId, Guardian)
     */
    public function getAppointmentsOfTeacher($teacherId) {
        $appointments = array();
        $data = self::$connection->selectValues("SELECT time_slot.id,bookable_slot.id,bookable_slot.eid,eltern.userid,eltern.name,eltern.vorname,user.email
			FROM time_slot,bookable_slot,eltern,user
			WHERE time_slot.id=bookable_slot.slotid
			AND bookable_slot.eid=eltern.id
			AND eltern.userid=user.id
			AND bookable_slot.lid='$teacherId' ORDER BY anfang");
        if (isset($data)) {
            foreach ($data as $d) {
                $parentId = $d[2];
                $userId = $d[3];
                $surname = $d[4];
                $name = $d[5];
                $email = $d[6];
                $parent = new Guardian($userId, $email, $parentId, $surname, $name);
                $parent->getESTChildren($this->getOptions()['limit']);
                $appointments[] = array("slotId" => $d[0], "bookingId" => $d[1], "parent" => $parent);
            }
        }
        
        return $appointments;
    }
    
    
    /**
     *retrieve all relevant booking Data for parent
     *
     * @param int parentId
     *
     * @return array("anfang","ende","teacher");
     */
    public function getBookingDetails($parentId) {
        $bookingDetails = array();
        $data = self::$connection->selectValues("SELECT anfang,ende,lid 
		FROM bookable_slot,time_slot
		WHERE bookable_slot.slotid = time_slot.id
		AND eid = '$parentId'
		ORDER BY anfang");
        if (isset($data)) {
            foreach ($data as $d) {
                $teacher = new Teacher(null, $d[2]);
                $bookingDetails[] = array("anfang" => $d[0], "ende" => $d[1], "teacher" => $teacher);
                unset($teacher);
            }
        }
        
        return $bookingDetails;
    }
    
    
    /**
     * login check for internal users (i.e., parents and admins)
     * @param $email
     * @param $password
     *
     * @return bool user exists in database and password is equal with the one in the database
     */
    public function passwordValidate($email, $password) {
        
        $email = self::$connection->escape_string($email);
        //$password = self::$connection->escape_string($userName);
        
        $data = self::$connection->selectAssociativeValues("SELECT password_hash from user WHERE email='$email' AND confirm_token is null");
        
        if ($data == null)
            return false;
        
        
        $data = $data[0];
        
        $pwd_hash = $data['password_hash'];
        
        
        return password_verify($password, $pwd_hash);
    }
    
    
    /**
     * @param $pid     array or int parents children ids (array[int] || int)
     * @param $email   string parents email
     * @param $pwd     string parents password
     * @param $name    string parent name
     * @param $surname string parent surname
     *
     * @return array newly created ids of parent (userid and parentid)
     */
    public function registerParent($email, $pwd, $name, $surname,$token) {
        
        $email = self::$connection->escape_string($email);
        $pwd = self::$connection->escape_string($pwd);
        $name = self::$connection->escape_string($name);
        $surname = self::$connection->escape_string($surname);
        $token = self::$connection->escape_string($token);
        
        $pwd = password_hash($pwd, PASSWORD_DEFAULT);
        
        $query = "INSERT INTO user (user_type, password_hash, email,confirm_token) VALUES (1,'$pwd', '$email','$token');";
        
        //Create parent in database and return eid
        $usrId = self::$connection->insertValues($query);
        
        $parentId = self::$connection->insertValues("INSERT INTO eltern (userid, vorname, name) VALUES ($usrId, '$name', '$surname');");
        
        //return eid
        return array("uid" => $usrId, "pid" => $parentId);
        
    }
    
    /**
     * checking the token for confirmation of registration
     * @param string token
     * @return string or null
     */
    public function confirmRegistration($token) {
        //check for token
        $data = self::$connection->selectValues('SELECT id,registered,email FROM user WHERE confirm_token = "'.$token.'"');
        if (empty($data) ) {
          return false;  
        } else {
            //token seems to be valid
            $userId = $data[0][0];
            $email = $data[0][2];
            //check for timeout
            $then = new DateTime($data[0][1]);
            $now = new DateTime(date("Y-m-d H:i:s"));
            $interval = $then->diff($now)->format("%d");
            if(intval($interval) > 0) {
                //confirmation time more than 1 day ago;
                //now delete the registered user from the database
                self::$connection->straightQuery("DELETE FROM user WHERE id = $userId");
                self::$connection->straightQuery("DELETE FROM eltern WHERE userid = $userId");
                return null;
            } else {
                //confirmation is correct
                //now delete token 
                self::$connection->straightQuery("UPDATE user set confirm_token = null WHERE id = $userId");
                return $email;
                }
         }
        
    }

    /**
     * Adds new student as child to parent
     * NEEDS TO BE IMPLEMENTED:
     * check if eid is already set, then use eid2
     * @param $parentId   int Parent ID
     * @param $studentIds array Student ID
     *
     * @return string success
     */
    public function parentAddStudents($parentId, $studentIds) {
        
        if (!is_array($studentIds))
            $studentIds = array($studentIds);
        
        $parent = $this->getParentByParentId($parentId);
        
        if ($parent == null)
            return false;
        
        $query = "";
        
        foreach ($studentIds as $id) {
            $student = $this->getStudentById($id);
            if ($student == null)
                return false;
            //check if a registration has already been made
            $data = self::$connection->selectValues("SELECT eid FROM schueler
            WHERE id='$id' AND eid is not null");
            if (!empty($data)){
                //one parent has already registered the pupil, 
                //so the second parent field needs to be entered
                    $fieldToUse = "eid2"; 
                } else {
                    $fieldToUse = "eid";
                }
            $query = "UPDATE schueler SET $fieldToUse=$parentId WHERE id='$id';";
            self::$connection->straightQuery($query);
        }
        
        
        return true;
    }
    
    
    /**
     * raise count to disable account after 3 failures
     *
     * @param $id        int userId
     * @param $increment bool whether or not to increase
     *
     * @return int amount of failed attempts
     */
    public function raiseLockedCount($id, $increment = true) {
        $data = self::$connection->selectValues("SELECT disabled_count, disabled_date FROM user WHERE id=" . $id);
        
        $resetTime = 5 * 60; // time in sec until unblock
        
        if ($data) {
            $disabledCount = $data[0][0];
            $disabledDate = $data[0][1];
            $d = DateTime::createFromFormat("ymd H:i:s", $disabledDate);
            
            if ($d instanceof DateTime) {
                $d = $d->getTimestamp();
            }
            
            if ($disabledCount > 0 && $disabledDate !== null && ($d + $resetTime - time()) <= 0) {
                self::$connection->straightQuery("UPDATE user SET disabled_count=0, disabled_date=NULL WHERE id = " . $id);
                $disabledCount = 0;
            } else {
                if ($increment) {
                    $disabledCount++;
                    self::$connection->straightQuery("UPDATE user SET disabled_count = disabled_count+1 WHERE id = " . $id);
                    if ($disabledCount >= 3) {
                        $now = date('ymd H:i:s');
                        self::$connection->straightQuery('UPDATE user SET disabled_date = "' . $now . '" WHERE id = ' . $id);
                    }
                }
            }
            
            return $disabledCount;
        }
        
        return 0;
        
        
        
    }
    
    /**
     * @param $usr string novell user
     * @param $pwd string novell passwd
     *
     * @returns array(string) [user => username case sensitive, type => student / teacher [, class => if student: students class]]
     * @throws Exception when error was thrown while connection to remote server or response was empty
     */
    public function checkNovellLogin($usr, $pwd) {
        
        $apiUrl = self::$connection->getIniParams()["ldap"]; //used to be hard coded "https://intranet.suso.schulen.konstanz.de/gpuntis/susointern.php"; 
        
        $headers = array('Authorization: Basic ' . base64_encode("$usr:$pwd"));
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		//uncomment the following line if SSL certificate issues are around
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); //fixme: ssl unsafe!!! -> is certificate correctly installed @ server? if yes we can remove this file and make everything save
        
        
        $result = utf8_encode(curl_exec($ch));
        if (curl_errno($ch)) {
            throw new Exception(curl_error($ch));
        }
        
        if ($result == false) {
            throw new Exception("Response was empty!");
        }
        
        $res = json_decode($result);
        
        ChromePhp::info("Response from ldap [$usr, $pwd]: " . json_encode($res));
        
        return $res;
        
        
    }
    
    /**
     * @param $token string captcha token
     *
     *                      Checks captcha response w/ google
     *
     * @return mixed response from api
     * @throws \Exception unsuccessful api connection
     */
    public function checkCaptcha($token) {
        $apiUrl = "https://www.google.com/recaptcha/api/siteverify";
        $key = $this->getIniParams()['captcha_private'];
        
        if(isset($this->getIniParams()['captcha_private_debug']) && self::$debugCaptcha)
        {
            $key = $this->getIniParams()['captcha_private_debug'];
        }
        
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "secret=$key&response=$token");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
        $result = utf8_encode(curl_exec($ch));
        if (curl_errno($ch)) {
            throw new Exception(curl_error($ch));
        }
        if ($result == false) {
            throw new Exception("Captcha response was empty!");
        }
        ChromePhp::info("Response from reCAPTCHA: $result");
        $result = json_decode($result);
        return $result;
    }
    
    
    /**
     *Termine aus Datenbank auslesen
     *
     * @param $includeStaff Boolean
     *
     * @return Array(Terminobjekt)
     */
    public function getEvents($isTeacher = null) {
        isset($isTeacher) ? $query = "SELECT typ,start,ende,staff,tNr,created FROM termine ORDER BY start" : $query = "SELECT typ,start,ende,staff,tNr,created FROM termine WHERE staff=0 ORDER BY start";
        $data = self::$connection->selectValues($query);
        foreach ($data as $d) {
            $termin = new Termin();
            $termin->createFromDB($d);
            $this->makeMonthsArray($termin->monatNum, $termin->monat, $termin->jahr);
            $termine[] = $termin->createFromDB($d);
        }
        
        return $termine;
    }
    
    /**
     *Ermittelt die kommenden Termine
     *
     * @param $staff boolean
     *
     * @return Array(Terminobjekte)
     */
    public function getNextDates($staff) {
        $staff ? $query = "SELECT typ,start,ende,staff,tNr,created FROM termine ORDER BY start" : $query = "SELECT typ,start,ende,staff,tNr,created FROM termine WHERE staff=0 ORDER BY start";
        $data = self::$connection->selectValues($query);
        $x = 0;
        foreach ($data as $d) {
            $termin = new Termin();
            $termine[$x] = $termin->createFromDB($d);
            $x++;
        }
        
        //Ermittle die neuesten Termine
        $today = date('d.m.Y');
        $added = strtotime("+21 day", strtotime($today));
        $limit = date("d.m.Y", $added);
        $todayTimestamp = strtotime($today);
        $limitTimestamp = strtotime($limit);
        
        $nextDates = array();
        $x = 0;
        foreach ($termine as $t) {
            if (strtotime($t->sday) >= $todayTimestamp && strtotime($t->sday) <= $limitTimestamp) {
                $nextDates[$x] = $t;
                $x++;
            }
        }
        
        return $nextDates;
    }
    
    
    /**
     *Monatsarray mit Terminen erstellen
     *
     * @param string Monat als Zahl
     * @param string Monat als Text
     * @param string jahr
     */
    private function makeMonthsArray($monatZahl, $monat, $jahr) {
        $noAdd = false;
        if (isset($this->monate)) {
            foreach ($this->monate as $m) {
                if ($m["mnum"] == $monatZahl) {
                    $noAdd = true;
                }
            }
        }
        if (!$noAdd) $this->monate[] = array("mnum" => $monatZahl, "mstring" => $monat, "jahr" => $jahr);
    }
    
    /**
     *Monatarray abrufen
     *
     * @return array(string) monate
     */
    public function getMonths() {
        return $this->monate;
    }
    
    /** Changes the password
     *
     * @param $usrId
     * @param $newPwd
     */
    public function changePwd($usrId, $newPwd) {
        $pwdhash = $pwd = password_hash($newPwd, PASSWORD_DEFAULT);
        self::$connection->straightQuery("UPDATE user SET password_hash='$pwdhash' WHERE id='$usrId'");
    }
    
    /** Change userdata
     *
     * @param $usrId
     * @param $name
     * @param $surname
     * @param $email
     * @param $news bool
     * @param $html bool
     *
     * @return bool success
     */
    public function updateUserData($usrId, $name, $surname, $email, $getnews = false, $htmlnews = false) {
        
        $name = self::$connection->escape_string($name);
        $surname = self::$connection->escape_string($surname);
        $email = self::$connection->escape_string($email);
        
        $getnews = $getnews == "true" ? 1 : 0;
        $htmlnews = $htmlnews == "true" ? 1 : 0;
        
        
        $check = self::$connection->selectValues("SELECT * FROM `user` WHERE email='$email' AND NOT id='$usrId'");
        
        if (isset($check[0]))
            return false;
        
        
        self::$connection->straightMultiQuery("UPDATE user SET email='$email' WHERE id='$usrId';
		UPDATE eltern SET vorname='$name', name='$surname', receive_news = '$getnews', htmlnews = '$htmlnews' WHERE userid='$usrId';");
        
        
        return true;
    }
    
    
    /**
     * get teacher's VPMail status
     *
     * @param int $id
     *
     * @return bool
     */
    public function getTeacherVpMailStatus($id) {
        $data = self::$connection->selectValues("SELECT receive_vpmail from lehrer WHERE id = '$id'");
        
        return $data[0][0];
    }
    
    /**
     * get teacher's NewsMail status
     *
     * @param int $id
     *
     * @return bool
     */
    public function getNewsMailStatus($id, $teacher) {
        $table = ($teacher) ? "lehrer" : "eltern";
        $idfield = ($teacher) ? "id" : "userid";
        $data = self::$connection->selectValues("SELECT receive_news from $table WHERE $idfield = '$id'");
        
        return $data[0][0];
    }
    
    /**
     * get teacher's NewsMail format
     *
     * @param int $id
     *
     * @return bool
     */
    public function getNewsHTMLStatus($id, $teacher) {
        $table = ($teacher) ? "lehrer" : "eltern";
        $idfield = ($teacher) ? "id" : "userid";
        $data = self::$connection->selectValues("SELECT htmlnews from $table WHERE $idfield = '$id'");
        
        return $data[0][0];
    }
    
    /**
     * get teacher's VP View status
     * false => view is set to personally relevant entries only
     *
     * @param int $id
     *
     * @return bool
     */
    public function getTeacherVpViewStatus($id) {
        $data = self::$connection->selectValues("SELECT vpview_all from lehrer WHERE id = '$id'");
        
        return $data[0][0];
    }
    
    /**
     * get student's courses
     *
     * @param int $id
     *
     * @return String
     */
    public function getStudentCourses($id) {
        $data = self::$connection->selectValues("SELECT kurse from schueler WHERE id = '$id'");
        
        return $data[0][0];
    }
	
	/**
	* returns all pupils whose surname starts with $startingWith
	* @param string 
	* @param int
	* @return array(StudentObject);
	*/
	public function getAllTaughtPupils($id,$startingWith) {
	$arr = array();
        $startingWith = self::$connection->escape_string($startingWith);
        $query = 'SELECT schueler.id,vorname,name,gebdatum,schueler.klasse FROM schueler,unterricht 
		WHERE unterricht.lid ='. $id.
		' AND unterricht.klasse = schueler.klasse 
		AND name LIKE "'.$startingWith.'%"';
		$data = self::$connection->selectAssociativeValues($query);
        if ($data != null && !empty($data))
            foreach ($data as $d) {
				//get the absence status of the student and add it to the array
			    $student = new Student($d['id'],$d['klasse'],$d['name'],$d['vorname'],$d['gebdatum']);
				$absenceState = (!empty($student->getAbsenceState()) ) ? true : false;
				
				$arr[] = array("absent" => $absenceState,"student" => $student );
			}
        return $arr;	
	}
	
	/**
	* get student absence state
	* @param int student id
	* @return bool
	*/
	public function getStudentAbsenceState($id) {
		return self::$connection->selectValues('SELECT aid FROM absenzen WHERE sid ='.$id.' AND ende >"'.date('Y-m-d').'"'); 	
		}
    
    /** Change teacherData
     *
     * @param      $usrId
     * @param bool $vpview
     * @param bool $vpmail
     * @param bool $newsmail
     * @param bool $newshatml
     *
     * @return bool
     */
    public function updateTeacherData($usrId, $vpview, $vpmail, $newsmail, $newshtml) {
        $vpview = $vpview == "true" ? 1 : 0;
        $newshatml = $newshtml == "true" ? 1 : 0;
        $newsmail = $newsmail == "true" ? 1 : 0;
        $vpmail = $vpmail == "true" ? 1 : 0;
        self::$connection->straightQuery("update lehrer set receive_vpmail = '$vpmail', vpview_all = '$vpview', receive_news = '$newsmail', htmlnews = '$newshtml' WHERE  id = '$usrId'");
        
        
        
        return true;
    }
    
    /** Change teacherData
     *
     * @param        $usrId
     * @param string $courseList
     *
     * @return bool
     */
    public function updateStudentData($usrId, $courseList) {
        
        $courseList = self::$connection->escape_string($courseList);
        self::$connection->straightQuery("update schueler set kurse = '$courseList' WHERE  id = '$usrId'");
        
        return true;
    }
    
    /**
     * Creates random token used for password forgotten
     *
     * @param $email
     *
     * @return array
     */
    public function generatePasswordReset($email) {
        $resp = array("success" => true, "key" => null, "message" => "OK");
        
        $email = self::$connection->escape_string($email);
        
        $randomKey = uniqid() . uniqid(); // random 26 char digit
        $user = $this->getUserByMail($email);
        
        if ($user == null || $user->getType() == 0) {
            $resp['success'] = false;
            $resp['message'] = 'No valid user email';
            
            return $resp;
        }
        $userId = $user->getId();
        
        self::$connection->straightQuery("INSERT INTO pwd_reset (token, uid, validuntil) VALUES ('$randomKey', '$userId', NOW() + INTERVAL 24 HOUR);");
        
        $resp['key'] = $randomKey;
        
        return $resp;
    }
    
    /**
     * @param $token
     * @param $newPwd
     *
     * @return array
     */
    public function redeemPasswordReset($token, $newPwd) {
        $resp = array("success" => true, "message" => "OK");
        
        $newPwd = self::$connection->escape_string($newPwd);
        $token = self::$connection->escape_string($token);
        
        $arr = self::$connection->selectAssociativeValues("SELECT COUNT(*) as count, uid FROM pwd_reset WHERE token='$token';")[0];
        if ($arr['count'] != "1") {
            $resp['success'] = false;
            $resp['message'] = "Invalid request";
        } else {
            $pwd = password_hash($newPwd, PASSWORD_DEFAULT);
            $uid = $arr['uid'];
            self::$connection->straightMultiQuery(
                "UPDATE user SET password_hash='$pwd' WHERE id='$uid';" .
                "DELETE FROM pwd_reset WHERE token='$token';"
            );
        }
        
        return $resp;
    }
    
    /**
     * @param $token string
     *
     * @return bool
     */
    public function checkPasswordResetToken($token) {
        $token = self::$connection->escape_string($token);
        $count = self::$connection->selectAssociativeValues("SELECT COUNT(*) as count FROM pwd_reset WHERE token='$token' AND validuntil > NOW()")[0]['count'];
        
        return $count == "1";
    }
    
    /**
     * Deletes all expired password reset token
     */
    public function cleanUpPwdReset() {
        self::$connection->straightQuery("DELETE FROM pwd_reset WHERE validuntil < NOW();");
    }
	
	
	/**
	* enter keyrequest in Database for further processing
	* @param string email
	* @param string surname
	* @param string birthday
	* param string klasse
	*/
	public function enterKeyRequestIntoDB($email, $name = null, $dob = null, $klasse = null){
	$user =	$this->getUserByEmail($email);
	$eid = $user->getParentId(); // this is the wrong ID (userID) -- fix it!
	self::$connection->straightQuery("INSERT INTO registration_request (`requestId`,`email`,`eid`,`name`,`dob`,`klasse`,`request_date`)
	VALUES ('','$email','$eid','$name','$dob','$klasse',CURRENT_TIMESTAMP)");
	return $user;
	}
    
    /*************************************************
     ********methods only used in CoverLesson module***
     *************************************************/
    
    /**
     * get all relevant days for display
     *
     * @param bool $isTeacher
     *
     * @return array [timestamp, dateAsString]
     */
    public function getVPDays($isTeacher) {
        $add = $isTeacher ? "" : "AND tag<3"; // how lovely
        $allDays = array();
        $data = self::$connection->selectValues("SELECT DISTINCT datum FROM vp_vpdata WHERE tag>0 $add ORDER BY datum ASC");
        if ($data != null) {
            foreach ($data as $day) {
                $allDays[] = array("timestamp" => $day[0], "dateAsString" => $this->getDateString($day[0]));
            }
        }
        
        return $allDays;
    }
    
    /**
     *returns a date in format "<Weekday> DD.MM.YYYY "
     *
     * @param string $date "YYYYMMDD"
     *
     * @return string
     */
    private function getDateString($date) {
        return $this->getWeekday($date) . ". " . $this->formatDateToGerman($date);
    }
    
    /**
     *returns day of the week for a given date
     *
     * @param String "YYYMMDD"
     *
     * @return String
     */
    private function getWeekday($date) {
        $date = getdate(DateTime::createFromFormat("Ynd", $date)->getTimestamp());
        
        $weekdays = array('So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa');
        $dayOfWeek = $date['weekday'];
        
        
        
        return $weekdays[$date['wday']];
    }
    
    
    /**
     * return date in format DD.MM.YYYY
     *
     * @param string $date in format "YYYYMMDD"
     *
     * @return String
     */
    private function formatDateToGerman($date) {
        return $date[6] . $date[7] . "." . $date[4] . $date[5] . "." . $date[0] . $date[1] . $date[2] . $date[3];
    }
    
    
    /**
     * return date in Format DayOfWeek, den dd.mm.YYYY
     *
     * @param $date String im Format YYYYMMDD
     *
     * @return String
     */
    public function formatDateToCompleteDate($date) {
        $year = $date[0] . $date[1] . $date[2] . $date[3];
        $month = $date[4] . $date[5];
        $day = $date[6] . $date[7];
        $daysOfWeek = array('Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag');
        $datum = getdate(mktime(0, 0, 0, $month, $day, $year));
        $dayOfWeek = $datum['wday'];
        $completeDate = $daysOfWeek[$dayOfWeek] . ", den " . $day . "." . $month . "." . $year;
        
        return $completeDate;
    }
    
    
    /**
     *returns date of last update
     *
     * @return String timestamp
     */
    public function getUpdateTime() {
        $data = self::$connection->selectValues("SELECT DISTINCT stand FROM vp_vpdata WHERE tag=1");
        if (count($data) > 0) {
            return $data[0][0];
        } else {
            return null;
        }
    }
    
    
    /**
     *get all current cover lessons for teachers
     *
     * @param boolean $showAll all coverLessons or only those of current user
     * @param Teacher $tchr
     * @param         array    ("datumstring","timestamp") $allDays
     *
     * @return array(coverLesson Object)
     */
    public function getAllCoverLessons($showAll, $tchr, $allDays) {
        $vertretungen = array();
        
        $add = "";
        if ($tchr != null && !$showAll) {
            $add = " AND (vLehrer='" . $tchr->getUntisName() . "' OR eLehrer='" . $tchr->getShortName() . "') ";
        }
        
        $order = "datum,vLehrer,stunde";
        
        if ($tchr == null)
            $order = "datum,klassen,stunde";
        
        foreach ($allDays as $day) {
            $datum = $day['timestamp'];
            $data = self::$connection->selectAssociativeValues("SELECT * FROM vp_vpdata 
			WHERE aktiv=true
			AND datum='$datum'
			$add
			ORDER BY $order ASC");
            
            if (is_array($data) ) {
                foreach ($data as $dayData) {
                    $coverLesson = new CoverLesson();
                    $coverLesson->constructFromDB($dayData);
                    $vertretungen[$day["timestamp"]][] = $coverLesson;
                }
            }
            
        }
        
        return $vertretungen;
    }
    
    /**
     * get all Cover Lessons for a teacher in Untis presented data (i.e. "--" and "selbst" will be shown)
     * no CoverLesson Object will be instantiated
     *
     * @param Teacher $teacher
     *
     * @return array()
     */
    public function getCoverLessonsByTeacher($teacher) {
        $coverLessons = array();
        $tname = self::$connection->escape_string($teacher->getUntisName());
        $tkurz = self::$connection->escape_string($teacher->getShortName());
        $data = self::$connection->selectAssociativeValues("SELECT *
		FROM vp_vpdata 
		WHERE (vLehrer='$tname' OR eLehrer='$tkurz')
		AND aktiv=true and tag>0 ORDER by datum,stunde");
        //echo " --- Anzahl Vertretungen: ".count($data).'<br>';
        if (count($data) > 0) {
            foreach ($data as $d) {
                $datum = $this->formatDateToCompleteDate($d['datum']);
                $coverLessons[] = array("vnr" => $d['vnr'], "Datum" => $datum, "Vertreter" => $d['vLehrer'], "Klassen" => $d['klassen'], "Stunde" => $d['stunde'], "Fach" => $d['fach'], "Raum" => $d['raum'], "statt_Lehrer" => $d['eLehrer'], "statt_Fach" => $d['eFach'], "Kommentar" => $d['kommentar']);
                
            }
        }
        
        return $coverLessons;
    }
    
    /**
     *get CoverLesson data by primary key
     *
     * @param int $id
     *
     * @return array()
     */
    public function getCoverLessonById($id) {
        $coverLesson = null;
        $data = self::$connection->selectAssociativeValues("SELECT * FROM vp_vpdata 
			WHERE vnr = '$id'");
        if (isset($data)) {
            $coverLesson = $data[0];
        }
        
        return $coverLesson;
    }
    
    
    /**
     *get all cover lessons for parents
     *
     * @param form  array(String) $classes
     * @param array ("datumstring","timestamp") $allDays
     *
     * @return array(coverLesson Object)
     */
    public function getAllCoverLessonsParents($classes, $allDays) {
        $vertretungen = null;
        //create query string to identify forms
        $classQuery = null;
        
        for ($i = 0; $i < sizeof($classes); $i++) {
            $class = self::$connection->escape_string($classes[$i]);
            
            $classQuery .= ($i == 0 ? " AND (" : " OR");
            $classQuery .= " klassen LIKE '%$class%'";
        }
        
        $classQuery .= ")";
        
        foreach ($allDays as $day) {
            $datum = $day['timestamp'];
            $query = "SELECT * FROM vp_vpdata 
			WHERE tag>0
			AND tag<3
			AND aktiv=true
			AND datum='$datum'
			$classQuery
			ORDER BY datum,stunde,klassen ASC";
            
            $data = self::$connection->selectAssociativeValues($query);
            if (is_array($data) ) {
                foreach ($data as $d) {
                    $coverLesson = new CoverLesson();
                    $coverLesson->constructFromDB($d);
                    $vertretungen[$day["timestamp"]][] = $coverLesson;
                }
            }
        }
        
        return $vertretungen;
    }
    
    /**
     *get all cover lessons for students
     *
     * @param StudentUser $student
     * @param             array ("datumstring","timestamp") $allDays
     *
     * @return array(coverLesson Object)
     */
    public function getAllCoverLessonsStudents($student, $allDays) {
        $vertretungen = null;
        
        
        foreach ($allDays as $day) {
            $datum = $day['timestamp'];
            $data = self::$connection->selectAssociativeValues("SELECT * FROM vp_vpdata 
			WHERE tag>0
			AND tag<3
			AND aktiv=true
			AND datum='$datum'
			AND vp_vpdata.klassen LIKE '%" . self::$connection->escape_string($student->getClass()) . "%'
			ORDER BY datum,stunde ASC");
            if (count($data) > 0) {
                foreach ($data as $d) {
                    $coverLesson = new CoverLesson();
                    $coverLesson->constructFromDB($d);
                    $vertretungen[$day["timestamp"]][] = $coverLesson;
                    unset($coverLesson);
                }
            }
            unset($data);
        }
        
        return $vertretungen;
    }
    
    
    /**
     *ermittle alle blockierten Räume
     *
     * @param $datum array QueryResult
     *
     * @return array(String,String)
     */
    
    public function getBlockedRooms($datum) {
        $roomstring = "";
        $blockedRooms = array();
        foreach ($datum as $d) {
            $date = $d['timestamp'];
            $data = self::$connection->selectValues("SELECT name FROM vp_blockierteraeume WHERE datum='$date' ");
            if (isset($data)) {
                foreach ($data as $room) {
                    if ($roomstring == "") {
                        $roomstring = $room[0];
                    } else {
                        $roomstring = $roomstring . ", " . $room[0];
                    }
                }
            }
            if ($roomstring == "") {
                $roomstring = "keine";
            }
            $roomstring = wordwrap($roomstring, 100, "<br />\n");
            $blockedRooms[$d['timestamp']] = $roomstring;
            $roomstring = "";
        }
        
        return $blockedRooms;
    }
    
    
    /**
     *ermittle alle abwesenden Lehrer
     *
     * @param $datum array QueryResult
     *
     * @return array(String,String)
     */
    
    public function getAbsentTeachers($datum) {
        $atstring = "";
        $absentTeachers = array();
        foreach ($datum as $d) {
            $date = $d['timestamp'];
            $data = self::$connection->selectValues("SELECT name FROM vp_abwesendeLehrer WHERE datum='$date' ");
            if (isset($data)) {
                foreach ($data as $t) {
                    if ($atstring == "") {
                        $atstring = $t[0];
                    } else {
                        $atstring = $atstring . ", " . $t[0];
                    }
                }
            }
            if ($atstring == "") {
                $atstring = "keine";
            }
            $atstring = wordwrap($atstring, 150, "<br />\n");
            $absentTeachers[$d['timestamp']] = $atstring;
            $atstring = "";
        }
        
        return $absentTeachers;
    }
    
    /**
     *get Primary key and email by Teacher untisname
     *
     * @param String $untisName
     *
     * @return array(String, Int)
     */
    public function getTeacherDataByUntisName($untisName) {
        $tchrData = array();
        $untisName = self::$connection->escape_string($untisName);
        $data = self::$connection->selectValues("SELECT email,id FROM lehrer WHERE untisName='$untisName'");
        if (is_array($data) ) {
            $tchrData = array("email" => $data[0][0], "id" => $data[0][1]);
            
            return $tchrData;
        } else {
            return null;
        }
        
    }
    
    /**
     *get Primary key and email by Teacher untisname
     * @param String $short
     * @return array(String, Int)
     */
    public function getTeacherDataByShortName($short) {
        $tchrData = array();
        $short = self::$connection->escape_string($short);
        $data = self::$connection->selectValues("SELECT email,id FROM lehrer WHERE kuerzel='$short' ");
        if (is_array($data) ) {
            $tchrData = array("email" => $data[0][0], "id" => $data[0][1]);
            
            return $tchrData;
        } else {
            return null;
        }
        
    }
    
    
    /**
     * @param $studentId ;
     *
     * @return array(String)
     */
    public function getCoursesOfStudent($studentId) {
        //MUST be separated from student's class
        $courses = array();
        $data = self::$connection->selectAssociativeValues("SELECT kurse FROM schueler WHERE id='$studentId'"); //Query missing - new table to be created [studentID,courseName]
        if (isset($data)) {
            if (isset($data[0])) {
                $data = $data[0];
            }
            
            foreach ($data as $d) {
                $courses[] = $d['kurse'];
            }
        }
        
        return $courses;
    }
	
	/**
	* get achildren of parent and absence state
	* @param array(StudentObject)
	* @return JSON array
	*/
	public function getChildrenAbsenceState($children) {
		$childrenArr = array();
		foreach ($children as $child) {
		$data = self::$connection->selectAssociativeValues('SELECT * FROM absenzen 
		WHERE sid='.$child->getId().
		' AND ende >="'.date('Y-m-d').'" AND beurlaubt = 0');
		if ($data) {
			$d = $data[0];
			$childrenArr[] = array("id"=>$child->getId(),
			"name"=>$child->getFullName(),
			"klasse"=>$child->getClass(),
			"absenceId" => $d['aid'],
			"absent"=>"true",
			"beginn" => $d['beginn'],
			"ende" => $d['ende'],
			"kommentar" => $d['kommentar'],
			"adminMeldung" => $d['adminMeldung'],
			"adminMeldungDatum" => $d['adminMeldungDatum'],
			"adminMeldungTyp" => $d['adminMeldungTyp'],
			"lehrerMeldung" => ($d['lehrerMeldung'] != 0 ) ? $this->getTeachernameByTeacherId($d['lehrerMeldung'])['krzl'] : 0,
			"lehrerMeldungDatum" => $d['lehrerMeldungDatum'],
			"elternMeldung" => $d['elternMeldung'],
			"elternMeldungDatum" => $d['elternMeldungDatum'],
			"entschuldigt" => $d['entschuldigt'],
			"single" => $d['single']
			);	
			} else {
			$childrenArr[] = 	array("id"=>$child->getId(),
			"name"=>$child->getFullName(),
			"klasse"=>$child->getClass(),
			"absent"=>"false");
			}
		}
		
		return json_encode($childrenArr);
	}
	
	/**
	* get leave of absence cases
	* @return JSON Array
	*/
	public function getLeaveOfAbsenceStudents(){
	$loaPupils = array();
	$data = self::$connection->selectAssociativeValues('SELECT * FROM absenzen,schueler 
	WHERE absenzen.sid = schueler.id
	AND beurlaubt = 1
	AND ende > "'.date('Y-m-d').'"' );
	if (!empty($data) ) {
		foreach ($data as $d) {
			//$pupil = new Student($d['sid'],$d['klasse'],$d['name'],$d['vorname'],$d['gebdatum'],$d['eid']);
			array_push($loaPupils, array("type" =>"absent",
			"absenceId" => $d['aid'],
			"id"=>$d['sid'],
			"name" => $d['name'].', '.$d['vorname'],
			"klasse" => $d['klasse'],
			"beginn" => $d['beginn'],
			"ende" => $d['ende'],
			"beurlaubt" => $d['beurlaubt'],
			"kommentar" => $d['kommentar'],
			"adminMeldung" => $d['adminMeldung'],
			"adminMeldungDatum" => $d['adminMeldungDatum']
			)
			);
		}	
	}
	return json_encode($loaPupils);	
	}
	
	
	/**
	* get absent students of current day
	* @return JSON array
	*/
	public function getAbsentStudents() {
		$absentPupils = array();
		$today = date('Y-m-d');
		$data = self::$connection->selectAssociativeValues('SELECT * FROM absenzen,schueler 
		WHERE absenzen.sid = schueler.id
		AND beginn <="'.$today.'" AND ende >="'.$today.'" ORDER BY schueler.name,schueler.vorname');
		if($data) {
				foreach ($data as $d) {
					$pupil = new Student($d['sid'],$d['klasse'],$d['name'],$d['vorname'],$d['gebdatum'],$d['eid'], $d['eid2']);
					array_push($absentPupils, array("type" =>"absent",
					"absenceId" => $d['aid'],
					"id"=>$d['sid'],
					"name" => $d['name'].', '.$d['vorname'],
					"klasse" => $d['klasse'],
					"beginn" => $d['beginn'],
					"ende" => $d['ende'],
					"beurlaubt" => $d['beurlaubt'],
					"kommentar" => $d['kommentar'],
					"adminMeldung" => ($d['adminMeldung'] != 0) ? $this->getUserById($d['adminMeldung'])->getEmail() : 0,
					"adminMeldungDatum" => $d['adminMeldungDatum'],
					"adminMeldungTyp" => $d['adminMeldungTyp'],
					"lehrerMeldung" => ($d['lehrerMeldung'] != 0) ? $this->getTeacherNameByTeacherId($d['lehrerMeldung'])['krzl'] : 0,
					"lehrerMeldungDatum" => $d['lehrerMeldungDatum'],
					"elternMeldung" => $d['elternMeldung'],
					"elternMeldungDatum" => $d['elternMeldungDatum'],
					"entschuldigt" => $d['entschuldigt'],
					"single" => $d['single']
					)
					);
				}
		}
		return $absentPupils;
		
	}
	
	/**
	* get data for absentee list to print out
	* @param int maximum Period to show (in workdays before current date)
	* @return JSON array
	*/
	public function getAbsenteeListData($maxPeriod) {
		$absentPupils = array();
		$absenteeForms = array();
		$data = self::$connection->selectAssociativeValues('SELECT * FROM absenzen,schueler 
		WHERE absenzen.sid = schueler.id
		AND ende >="'.date("Y-m-d", strtotime('-'.$maxPeriod.' weekdays') ).
		'" ORDER BY schueler.klasse,schueler.name,schueler.vorname');
		if(!empty($data)) {
				foreach ($data as $d) {
					if (!in_array($d['klasse'],$absenteeForms) ) {
						array_push($absenteeForms,$d['klasse']);	
						}
					$pupil = new Student($d['sid'],$d['klasse'],$d['name'],$d['vorname'],$d['gebdatum'],$d['eid'], $d['eid2']);
					array_push($absentPupils, array("type" =>"absent",
					"absenceId" => $d['aid'],
					"id"=>$d['sid'],
					"name" => $d['name'].', '.$d['vorname'],
					"klasse" => $d['klasse'],
					"beginn" => $d['beginn'],
					"ende" => $d['ende'],
					"beurlaubt" => $d['beurlaubt'],
					"kommentar" => $d['kommentar'],
					"single" => $d['single']/*,
					"adminMeldung" => $d['adminMeldung'],
					"adminMeldungDatum" => $d['adminMeldungDatum'],
					"adminMeldungTyp" => $d['adminMeldungTyp'],
					"lehrerMeldung" => $d['lehrerMeldung'],
					"lehrerMeldungDatum" => $d['lehrerMeldungDatum'],
					"elternMeldung" => $d['elternMeldung'],
					"elternMeldungDatum" => $d['elternMeldungDatum'],
					"entschuldigt" => $d['entschuldigt']*/
					)
					);
				}
		}
		
		return array("formsWithAbsences" => $absenteeForms,"absentPupils" => $absentPupils);
		
	}
	
	
	/**
	* get missing absence excuse
	* @return json
	*/
	public function getMissingExcuses() {
		
		$absentPupils = array();
		$today = date('Y-m-d');
		$data = self::$connection->selectAssociativeValues('SELECT * FROM absenzen,schueler 
		WHERE absenzen.sid = schueler.id
		AND ende <"'.$today.'" AND entschuldigt = "0000-00-00" ORDER BY schueler.name,schueler.vorname');	
		if($data) {
				foreach ($data as $d) {
					
					$pupil = new Student($d['sid'],$d['klasse'],$d['name'],$d['vorname'],$d['gebdatum'],$d['eid'], $d['eid2']);
					array_push($absentPupils, array("type" =>"missingExcuse",
					"absenceId" => $d['aid'],
					"id"=>$d['sid'],
					"name" => $d['name'].', '.$d['vorname'],
					"klasse" => $d['klasse'],
					"beginn" => $d['beginn'],
					"ende" => $d['ende'],
					"kommentar" => $d['kommentar'],
					"adminMeldung" => $d['adminMeldung'],
					"adminMeldungDatum" => $d['adminMeldungDatum'],
					"adminMeldungTyp" => $d['adminMeldungTyp'],
					"lehrerMeldung" => (isset($d['lehrerMeldung'])) ? $this->getTeacherNameByTeacherId($d['lehrerMeldung'])['krzl'] : 0,
					"lehrerMeldungDatum" => $d['lehrerMeldungDatum'],
					"elternMeldung" => $d['elternMeldung'],
					"elternMeldungDatum" => $d['elternMeldungDatum'],
					"entschuldigt" => $d['entschuldigt'],
					"single" => $d['single']
					)
					);
				}
		}
		return $absentPupils;
			
	}
	
	
	/**
	* check if absence existed the previouzs day
	* @param int pupilId
	* @param string date
	*/
	public function getPreviousDayAbsence($id,$date) {
	$dayBefore = date('Y-m-d',(strtotime ( '-1 day' , strtotime ( $date) ) ));
	$data = self::$connection->selectValues('SELECT aid FROM absenzen 
	WHERE sid ='. $id .' AND ende >= "'.$dayBefore.'" AND beurlaubt = 0');
	
	
	if ($data) {
	return $data[0][0];	
	} else {
	return null;	
	}
	}
	
	/**
	* add to existing absence - prolong
	* @param int aid
	* @param string startdate
	* @param string enddate
	* @user int optinal
	* @user int mode
	*/
	public function addToAbsence($aid,$end,$user = null,$mode = false) {
		//must consider user who enters and in case of admin also type
	if ($user) {
	if ($mode)  {
		//admin
		$query = 'UPDATE absenzen set ende = "'.$end.'",adminMeldung='.$user.',adminMeldungTyp ="'.$mode.'",adminMeldungDatum="'.date('Y-m-d H:m:s').'" WHERE aid = '.$aid;
	} else {
		//teacher
		$query = 'UPDATE absenzen set ende = "'.$end.'",lehrerMeldung='.$user.',lehrerMeldungDatum="'.date('Y-m-d H:m:s').'" WHERE aid = '.$aid;
	}
	} else {
		//parent
		$query = 'UPDATE absenzen set ende = "'.$end.'" WHERE aid = '.$aid;	
	}
	
	self::$connection->straightQuery($query); 
		
	}
	
	/**
	* add to single lesson absence
	* @param int aid
	* @param array(int) lessons
	*/
	public function addToSingleAbsence($aid, $missingLessons) {
		//add every missing lesson as individual dataset
	}
	
	/**
	* enter absent student
	* @param int sid
	* @param int notice method
	* @param string end date
	* @param string comment
	* @param int user id
	* @param int user type
	*/
	public function enterAbsentPupil($id ,$start,$end,$comment, $userId,$method = null,$who = 1,$loa = 0,$single = null) {
	if	(isset($single) ) {
		$insert = "";
					foreach ($single as $s) {
						$insert .= ",`p".$s."`";
						$value .= ",'1'";
					}
						Debug::writeDebugLog(__method__,"Insert ".$insert." Value". $value);
	}
	
	$modus = false;
	switch($who) {
		case 1://admin
		$whoField = "adminMeldung";
		$whenField = "adminMeldungDatum";
		$modus = true;
		break;
		case 2:
		$whoField = "elternMeldung";
		$whenField = "elternMeldungDatum";
		break;
		case 3:
		$whoField = "lehrerMeldung";
		$whenField = "lehrerMeldungDatum";
		break;
	}
	if ($end == 0) 
		$end = $start;
	if($modus) {
		$queryAdd = ',`adminMeldungTyp`)';	
		$valueAdd = ",'$method')";
		} else {
		$queryAdd = $valueAdd = ')';
		}	
		
	
	/*$singleMark = (isset($single) ) ? true : false;
	$query = "INSERT INTO absenzen (`aid`,`sid`,`single`,`beginn`,`ende`,`kommentar`,`beurlaubt`,`".$whoField."`,`".$whenField."`".
	$queryAdd. 
	"VALUES ('','$id','$singleMark','$start','$end','$comment','$loa','$userId',CURRENT_TIMESTAMP".
	$valueAdd;*/
	$now = date('Y-m-d H:m:s');
	$query = "INSERT INTO absenzen (`aid`,`sid`,`beginn`,`ende`,`kommentar`,`beurlaubt`,`".$whoField."`,`".$whenField."`".
	$queryAdd. 
	"VALUES ('','$id','$start','$end','$comment','$loa','$userId','$now'".
	$valueAdd;
	
	self::$connection->straightQuery($query);	
	/*if ($singleMark) {
		//Add the single lessons to database
	}*/
	Debug::writeDebugLog(__method__,"INSERT INTO absenzen (`aid`,`sid`,`beginn`,`ende`,`kommentar`,`beurlaubt`,`".$whoField."`,`".$whenField."`".
	$queryAdd. 
	"VALUES ('','$id','$start','$end','$comment','$loa','$userId',CURRENT_TIMESTAMP".
	$valueAdd);
	return true;
	}
	
	/**
	* enterSingleLessonAbsence
	* @param int userId  // use Object instead???
	* @param int id
	* @param int sid (studentId)
	* @param string start
	* @param int period
	* @param int comment
	* @return array
	*/
	public function enterSingleLessonAbsence($user,$id,$sid,$start,$period,$comment) {
	
	$absenceState = array();
	$absenceState['action'] = "enter";
	$data = null;
	$userId = $user->getId();
	if ($user instanceOf Teacher) {
			$enteredBy = 'lehrerMeldung';
			$enteredAt = 'lehrerMeldungDatum';
			} elseif ($user instanceOf Admin) {
			$enteredBy = 'adminMeldung';
			$enteredAt = 'adminMeldungDatum';	
			}
			$enteredAtValue = date('Y-m-d H:m:s');
	
	//check if general absence is already entered7
	if(isset($id)) {
	$data = self::$connection->selectAssociativeValues('SELECT * FROM absenzen 
	WHERE aid = '.$id.' 
	AND beginn = "'.$start.'" 
	AND single = 1');
	}
	if (isset($data) ) {
		Debug::writeDebugLog(__method__,"aid single exists");
		//Absence alread entered in table absenzen
		$absenceId = $data[0]['aid'];
		
		//check if already existing then delete
		$absence = self::$connection->selectAssociativeValues("SELECT said FROM absenzen_single 
		WHERE aid = $absenceId AND period = $period");
		if (!empty($absence) ) {
			Debug::writeDebugLog(__method__,"absence for this very period exists,i.e. will be deleted");
			//needs to be deleted
			self::$connection->straightQuery('DELETE FROM absenzen_single WHERE said = ' . $absence[0]['said'] );
			$absenceState['action'] = "delete";
			} else {
			Debug::writeDebugLog(__method__,"single absence will be entered on basis of existing aid by ". $userId);	
			//enter absence into single_absence table
				
			self::$connection->straightQuery("INSERT INTO absenzen_single (`said`,`aid`,`period`,
			`$enteredBy`,`$enteredAt`) VALUES
			('','$absenceId','$period','$userId','$enteredAtValue')");
			}
		} else {
		
		//New absence needs to be created, i.e. this single lesson absence is the first at that date
		//enter general absence into absenzen table	
		Debug::writeDebugLog(__method__,"new entry in absenzen will be made");	
		$absenceId = self::$connection->insertValues("INSERT INTO absenzen (`aid`,`sid`,`beginn`,`ende`,`single`,`kommentar`) 
		VALUES ('','$sid','$start','$start','1','$comment')");
		//enter absence into single_absence table
		Debug::writeDebugLog(__method__,"new entry in absenzen_single will be made with aid = ".$absenceId);	
		self::$connection->straightQuery("INSERT INTO absenzen_single (`said`,`aid`,`period`,
		`$enteredBy`,`$enteredAt`) VALUES
		('','$absenceId','$period','$userId','$enteredAtValue')");
		
		}
	
	//read all absent lessons from absence
	$absenceState['aid'] = $absenceId;
	$absences = self::$connection->selectAssociativeValues("SELECT * FROM absenzen_single WHERE aid = $absenceId");
	if (!empty($absences) ) {
	$absenceState['missingPeriods'] = array();
	foreach ($absences as $a) {
		array_push($absenceState['missingPeriods'],array(
		"period" => $a['period'],
		"lehrerMeldung" => (isset($a['lehrerMeldung'])) ? $this->getTeacherNameByTeacherId($a['lehrerMeldung'])['krzl'] : 0,
		"lehrerMeldungDatum" => $a['lehrerMeldungDatum'],
		"adminMeldung" => $a['adminMeldung'],
		"adminMeldungDatum" => $a['adminMeldungDatum'])
		);
		}
	}else {
	return null;	
	}
	
	return $absenceState;	
	}
	
	
	
	
	/**
	* enter excuse for absence
	* @param int id primary key
	* @param string excuss in date
	* @param string comment
	*/
	public function enterExcuse($id,$date,$comment) {
	//Debug::writeDebugLog(__method__,'UPDATE absenzen SET entschuldigt = "'.$date.'", ekommentar = "'.$comment.'" WHERE aid ='. $id);
	self::$connection->straightQuery('UPDATE absenzen SET entschuldigt = "'.$date.'", ekommentar = "'.$comment.'" WHERE aid ='. $id);
	}
	
	/**
	*edit absence entry
	* @param int aid
	* @param string start date
	* @param string end date
	* @param string comment
	* @param int notice method
	* @param int user id
	*/
	public function editAbsence($aid,$start,$end,$comment,$method,$id){
	
	
	$queryAdd = '';
	$whoField = "adminMeldung";
	$whenField = "adminMeldungDatum";
	$queryAdd = ',adminMeldungTyp = '.$method;	
	
	if ($end == 0) 
		$end = $start;
	
	$query = 'UPDATE absenzen set beginn="'.$start.'",ende="'.$end.
	'",kommentar="'.$comment.'",'.$whoField.'='.$id.','.$whenField.'=CURRENT_TIMESTAMP'.$queryAdd.' WHERE aid='.$aid;
	
	//Debug::writeDebugLog(__method__,$query);
	self::$connection->straightQuery($query);
	
	}	
	
	/**
	* delete pupil absence - not revertable
	* @param int id
	*/
	public function deleteAbsence($id) {
	self::$connection->straightQuery("DELETE FROM absenzen WHERE aid = $id"); 	
	}
	
	/**
	* get single student Data Set from absenzen
	* @param string type
	* @param int absenceId
	* @return array
	*/
	public function getStudentDataSet($type,$aid) {
	$data = self::$connection->selectAssociativeValues('SELECT * FROM absenzen,schueler 
		WHERE absenzen.sid = schueler.id
		AND aid ='.$aid);
	$d = $data[0];
	return array("type" =>$type,
		"absenceId" => $d['aid'],
		"id"=>$d['sid'],
		"name" => $d['name'].', '.$d['vorname'],
		"klasse" => $d['klasse'],
		"beginn" => $d['beginn'],
		"ende" => $d['ende'],
		"kommentar" => $d['kommentar'],
		"adminMeldung" => $d['adminMeldung'],
		"adminMeldungDatum" => $d['adminMeldungDatum'],
		"adminMeldungTyp" => $d['adminMeldungTyp'],
		"lehrerMeldung" => $d['lehrerMeldung'],
		"lehrerMeldungDatum" => $d['lehrerMeldungDatum'],
		"elternMeldung" => $d['elternMeldung'],
		"elternMeldungDatum" => $d['elternMeldungDatum'],
		"entschuldigt" => $d['entschuldigt']
		);
	}
	
	/**
	* get taught students of a teacher including all absence data
	* @param int teacherId
	* @return array
	*/
	public function getTaughtStudentsOfTeacher($id) {
	$students = array();
	$data = self::$connection->selectAssociativeValues('SELECT schueler.id,name,vorname,schueler.klasse 
	FROM schueler,unterricht
	WHERE schueler.klasse = unterricht.klasse
	AND unterricht.lid ='.$id.'
	ORDER BY schueler.name,schueler.vorname');
	if (!empty($data)) {
			foreach ($data as $d) {
			//get absence state
			$absentData = self::$connection->selectAssociativeValues('SELECT * FROM absenzen 
			WHERE sid ='.$d['id'].
			' AND beginn<="'.date('Y-m-d').'" 
			AND ende >="'.date('Y-m-d').'"');
			if (!empty($absentData)) {
				
				$absentData = $absentData[0];
				$absTeacher = ($absentData['lehrerMeldung'] != 0 ) ? $this->getTeacherNameByTeacherId($absentData['lehrerMeldung'])['krzl'] : 0 ;
				
				$absent = true;
				$students[] = array("absent"=>true,
				"id"=>$d['id'],
				"name" => $d['name'].', '.$d['vorname'],
				"klasse" => $d['klasse'],
				"absenceId" => $absentData['aid'],
				"beginn" => $absentData['beginn'],
				"ende" => $absentData['ende'],
				"kommentar" => $absentData['kommentar'],
				"adminMeldung" => $absentData['adminMeldung'],
				"adminMeldungDatum" => $absentData['adminMeldungDatum'],
				"adminMeldungTyp" => $absentData['adminMeldungTyp'],
				"elternMeldung" => $absentData['elternMeldung'],
				"elternMeldungDatum" => $absentData['elternMeldungDatum'],
				"lehrerMeldung" => $absTeacher,
				"lehrerMeldungDatum" => $absentData['lehrerMeldungDatum'],
				"entschuldigt" =>$absentData['entschuldigt'],
				"single" =>$absentData['single'])
				;
				
				} else {
				$absent = false;
				$students[] = array("absent"=>false,
				"id"=>$d['id'],
				"name" => $d['name'].', '.$d['vorname'],
				"klasse" => $d['klasse']);				
				}
			
			}
			
		return $students;
	} else 
		return null;
	
	}
    
    
    /**********************************************************
     ******functions for CoverLesson Module in data transmission
     ***********************************************************/
    
    /**
     * Bereite DB fuer neue Eintraege vor
     *Setze alle Eintraege des Datums der geparsten Datei auf inaktiv
     *Setze das tag Feld auf 0, damit die nur die aktuell geparsten Dateien (Tage) eingetragen werden
     *
     * @param $dat
     */
    public function prepareForEntry($dat) {
        $dArr = explode(';', self::$connection->escape_string($dat));
        $datum = $dArr[0];
        $file = $dArr[1];
        $today = date('Ymd');
        self::$connection->straightQuery("UPDATE vp_vpdata SET aktiv=false WHERE datum='$datum'");
        //Nur bei der ersten geparsten datei wird das tag feld auf Null gesetzt
        if ($file == 1) {
            self::$connection->straightQuery("UPDATE vp_vpdata SET tag=0 WHERE datum<'$datum'");
        }
    }
    
    /**
     *fuege abwesende lehrer in DB ein
     *
     * @param absT String im Format YYYYMMDD;Lehrername
     */
    public function insertAbsentee($absT) {
        $arr = explode(";", self::$connection->escape_string($absT));
        $datum = $arr[0];
        $rest = $arr[1];
        $arr = explode(",", $rest);
        //DELETE all entries for this date in order to be renewed
        self::$connection->straightQuery("DELETE FROM vp_abwesendeLehrer WHERE datum='$datum' ");
        foreach ($arr as $r) {
            self::$connection->insertValues("INSERT INTO vp_abwesendeLehrer (`alNr`,`datum`,`name`) 
				VALUES ('','$datum','$r')");
            //Response Meldung an C# Programm
            //echo "INSERT INTO abwesendeLehrer (`alNr`,`datum`,`name`) VALUES ('','$datum','$r')";
        }
    }
    
    
    /**
     *fuege blockierte Raeume in DB ein
     *
     * @param bR String im Format YYYYMMDD;Raumnummer
     */
    public function insertBlockedRoom($bR) {
        $arr = explode(";", self::$connection->escape_string($bR));
        $datum = $arr[0];
        $rest = $arr[1];
        //DELETE all entries for this date in order to be renewed
        self::$connection->straightQuery("DELETE FROM vp_blockierteraeume WHERE datum='$datum' ");
        $arr = explode(",", $rest);
        foreach ($arr as $r) {
            self::$connection->insertValues("INSERT INTO vp_blockierteraeume (`brNr`,`datum`,`name`) 
			VALUES ('','$datum','$r')");
            //Response Meldung an C# Programm
            //echo "INSERT INTO blockierteraeume (`brNr`,`datum`,`name`) VALUES ('','$datum','$r')";
        }
        
    }
    
    /**
     *fuege Vertretungsstunde ein
     *
     * @param String
     */
    public function insertCoverLesson($content) {
        $POSTCoverL = new CoverLesson();
        $POSTCoverL->constructFromPOST($content);
        
        //Prüfe ob dieser Eintrag bereits vorhanden ist
        $data = self::$connection->selectAssociativeValues("SELECT * FROM vp_vpdata WHERE id='$POSTCoverL->id' ");
        if (count($data) > 0) {
            $DBCoverL = new CoverLesson();
            $DBCoverL->ConstructFromDB($data[0]);
            $pk = $DBCoverL->primaryKey;
            self::$connection->straightQuery("UPDATE vp_vpdata SET aktiv=true,tag=$POSTCoverL->tag,stand='$POSTCoverL->stand' WHERE vnr='$pk'");
            //prüfe ob nur Kommentar geaendert ist
            if (strcmp($POSTCoverL->kommentar, $DBCoverL->kommentar) !== 0) {
                $k = $POSTCoverL->kommentar;
                //Komentar updaten
                self::$connection->straightQuery("UPDATE vp_vpdata SET kommentar='$k',aktiv=true,changed_entry=CURRENT_TIMESTAMP WHERE vnr='$pk'");
            }
            if ($POSTCoverL->changedEntry == 1) {
                //update all fields except emailed - this is a change to the former version where emailed was set to 0
                //$POSTCoverL->emailed = 0;
                $POSTCoverL->aktiv = true;
                self::$connection->straightQuery("UPDATE vp_vpdata SET tag=$POSTCoverL->tag,datum='$POSTCoverL->datum',vlehrer=' $POSTCoverL->vTeacher',
				klassen='$POSTCoverL->klassen',stunde='$POSTCoverL->stunde',fach='$POSTCoverL->vFach',raum='$POSTCoverL->vRaum',
				eLehrer='$POSTCoverL->eTeacherKurz',eFach='$POSTCoverL->eFach',kommentar='$POSTCoverL->kommentar',id='$POSTCoverL->id',aktiv=$POSTCoverL->aktiv,
				stand='$POSTCoverL->stand',changed_entry=CURRENT_TIMESTAMP WHERE vnr=$pk");
            }
        } else {
            //Eintrag in Datenbank
            $POSTCoverL->aktiv = true;
            self::$connection->insertValues("INSERT into vp_vpdata (`vnr`,`tag`,`datum`,`vLehrer`,`klassen`,`stunde`,`fach`,`raum`,`eLehrer`,`eFach`,`kommentar`,`id`,`aktiv`,`stand`,`changed_entry` )
			VALUES ('','$POSTCoverL->tag','$POSTCoverL->datum','$POSTCoverL->vTeacher','$POSTCoverL->klassen','$POSTCoverL->stunde','$POSTCoverL->vFach','$POSTCoverL->vRaum',
			'$POSTCoverL->eTeacherKurz','$POSTCoverL->eFach','$POSTCoverL->kommentar','$POSTCoverL->id','$POSTCoverL->aktiv','$POSTCoverL->stand',CURRENT_TIMESTAMP)");
        }
    }
    
    
    /**
     *Debugging LogFile Entry for CoverLessonModule
     *
     * @param String
     */
    public function writeToVpLog($text) {
        $f = fopen("vpaction.log", "a");
        $text .= "\r\n";
        fwrite($f, $text);
        fclose($f);
    }
    
    
    /**
     * lese Emailbedarf aus
     *
     * @return mailListLehrer Array(Teacher)
     */
    public function getMailList() {
        $mailListLehrer = array();
        //Lese Emailbedarf für Aktualisierung (neue Vertretungen )
        $data = self::$connection->selectValues("SELECT DISTINCT lehrer.id,email FROM vp_vpdata,lehrer 
		WHERE changed_entry >= emailed
		AND vp_vpdata.vLehrer=lehrer.untisName
		AND lehrer.receive_vpmail IS TRUE
		AND aktiv=TRUE AND vlehrer NOT LIKE '%--%' 
		AND vlehrer NOT LIKE '%selbst%' 
		AND tag>0");
        
        if (count($data) > 0) {
            foreach ($data as $d) {
                //Diese Lehrer müssen eine Email erhalten
                $mailListLehrer[] = $this->addToEmailList($d[0], $d[1]);
            }
        }
        //bei diesen Lehrern entfällt etwas
        $data = self::$connection->selectValues("SELECT DISTINCT lehrer.id,email 
		FROM vp_vpdata,lehrer 
		WHERE vp_vpdata.eLehrer=lehrer.kuerzel
		AND changed_entry >= emailed
		AND lehrer.receive_vpmail IS TRUE
		AND aktiv=TRUE 
		AND (vlehrer LIKE \"%--%\" OR vlehrer LIKE \"%selbst%\") AND tag>0 ");
        if (count($data) > 0) {
            foreach ($data as $d) {
                //Prüfe ob dieser Lehrer schon in der EmailListe ist
                if ($this->mustAddToList($mailListLehrer, $d[0])) {
                    $mailListLehrer[] = $this->addToEmailList($d[0], $d[1]);
                }
            }
        }
        //bei diesen Lehrern wurde eine Vertretung gestrichen
        $data = self::$connection->selectValues("SELECT DISTINCT lehrer.id,email FROM vp_vpdata,lehrer 
		WHERE aktiv=FALSE
		AND vp_vpdata.vLehrer=lehrer.untisName
		AND lehrer.receive_vpmail IS TRUE
		AND vlehrer NOT LIKE '%--%' 
		AND vlehrer NOT LIKE '%selbst%' 
		AND tag>0");
        if (count($data) > 0) {
            foreach ($data as $d) {
                //Prüfe ob dieser Lehrer schon in der EmailListe ist
                if ($this->mustAddToList($mailListLehrer, $d[0])) {
                    $mailListLehrer[] = $this->addToEmailList($d[0], $d[1]);
                }
            }
        }
        
        return $mailListLehrer;
    }
    
    /**
     * adds a Teacher Object to the Emaillist
     *
     * @param int teacherId
     *
     * @return Teacher Object
     */
    private function addToEmailList($id, $email) {
        $teacher = new Teacher($email, $id); //adapt to Teacher class constructor
        $teacher->getData();
        $teacher->setVpInfoDate($this->getUpdateTime());
        
        return $teacher;
    }
    
    /**
     *
     * check if teacher must be added to EmailList
     *
     * @param array ()
     * @param int
     *
     * @return bool
     */
    private function mustAddToList($list, $id) {
        if (count($list) == 0) {
            return true;
        }
        foreach ($list as $l) {
            if ($l->getId() == $id) {
                //already included
                return false;
                break;
            }
        }
        
        return true;
    }
    
    
    /**
     *Trage Datum des Email Versands in die Datenbank ein
     *
     * @param entry Id des CoverLesson Datensatzes
     */
    public function updateVpMailSentDate($entry) {
        self::$connection->straightQuery("UPDATE vp_vpdata set emailed = CURRENT_TIMESTAMP WHERE vnr='$entry'");
    }
    
    /**
     * delete all inactive entries in coverLessontable
     */
    public function deleteInactiveEntries() {
        self::$connection->straightQuery("DELETE FROM vp_vpdata WHERE aktiv=FALSE");
    }
    
    
    /**
     * create mail content for automated cover lesson email
     *
     * @param Teacher $teacher
     *
     * @return String
     */
    public function makeHTMLVpMailContent($teacher) {
        $coverLessonNrs = array();
        $data = $this->getCoverLessonsByTeacher($teacher);
        $linkStyle = 'style="font-family:Arial,Sans-Serif;font-size:12px;font-weight:bold;color: #009688;font-decoration:underline;"';
        $vnArr = array();
        $content = mb_convert_encoding('<table><tr><td style="color:#000000;font-family:Arial,Sans-Serif;font-weight:bold;font-size:14px;">Übersicht für ' .
            $teacher->getSurname() . ', ' . $teacher->getName() . '</td><td style="color:#000000;font-family:Arial,Sans-Serif;font-weight:bold;font-size:9px;"> 
	(Stand: ' . $teacher->getVpInfoDate() . ')</td></tr></table><br/>', 'UTF-8');
        if (!isset($data)) {
            $content .= "<p><b>Keine Vertretungen!</b></p>";
        } else {
            //make headers
            $content .= '<table>';
            $v = $data[0];
            $content .= '<tr style="font-family:Arial,Sans-Serif;font-size:12px;font-weight:bold;color:#ffffff; background-color: #009688;">';
            $colcounter = 0;
            foreach ($v as $key => $value) {
                if ($colcounter > 0) {
                    $content .= '<td><b>' . $key . '</b></td>';
                }
                $colcounter++;
            }
            $content .= '</tr>';
            //lines containing cover lessons
            $zeile = true;
            foreach ($data as $v) {
                $colcounter = 0;
                if ($zeile) {
                    $style = 'style="font-family:Arial,Sans-Serif;font-size:12px; background-color:#cccccc;';
                    $zeile = false;
                } else {
                    $style = 'style="font-family:Arial,Sans-Serif;font-size:12px; background-color:#eeeeee;';
                    $zeile = true;
                }
                if ($v["Vertreter"] == $teacher->getUntisName()) {
                    $style = $style . 'color:#009688;"';
                } else {
                    $style = $style . 'color:#000000;"';
                }
                $content .= '<tr ' . $style . '>';
                foreach ($v as $key => $value) {
                    if ($colcounter > 0) {
                        $content .= '<td>' . $value . '</td>';
                    } else {
                        $coverLessonNrs[] = $v["vnr"];
                    }
                    $colcounter++;
                }
                $content .= '</tr>';
            }
        }
        
        $content .= '</table>';
        $subscriptionInfo = '<p style="font-family:Arial,Sans-Serif;font-size:12px; font-weight:bold;">' . mb_convert_encoding('<br><br>Diese Email wurde automatisch versendet.
	 Die Einstellung zum Emailversand können Sie jederzeit in der <a ' . $linkStyle . ' href="http://www.suso.schulen.konstanz.de/intern">Suso-Intern-Anwendung</a> (Login erforderlich) ändern.<br>
	Bitte melden Sie Unregelmäßigkeiten oder Fehler im Emailversand.<br><br>Vielen Dank für Ihre Unterstützung!', 'UTF-8') . '</p>';
        
        $teacher->setCurrentCoverLessonNrs($coverLessonNrs);
        
        return $mailContent = $content . $subscriptionInfo . '<br>';
        
    }
    
    /*******************************
     ****Newsletter Functionality****
     *******************************/
    
    /**
     * Get Newsletter Data
     *
     * @param int id
     *
     * @return array
     */
    public function getNewsletterData($id) {
        $data = self::$connection->selectValues("SELECT publish, text, schoolyear, lastchanged, sent
		FROM newsletter where newsid = '$id'");
        
        return array("publishdate" => $data[0][0], "text" => $data[0][1], "schoolyear" => $data[0][2],
                     "lastchanged" => $data[0][3], "sent" => $data[0][4]);
        
    }
    
    
    
    /**
     * Get Newsletter School years
     *
     * @return array
     */
    public function getNewsYears() {
        $data = self::$connection->selectValues("SELECT DISTINCT schoolyear FROM newsletter ORDER BY schoolyear");
        $schoolyears = array();
        if ($data != null) {
            foreach ($data as $d) {
                $schoolyears[] = $d[0];
            }
        }
        
        return $schoolyears;
    }
    
    /**
     * get NewsletterIds by schoolyear
     *
     * @param array (String)
     *
     * @return array
     */
    public function getNewsIds() {
        $data = self::$connection->selectValues("SELECT newsid FROM newsletter
			ORDER BY schoolyear,publish");
        
        if ($data == null) {
            $news = array();
        } else {
            
            foreach ($data as $d) {
                $news[] = $d;
            }
        }
        
        return $news;
    }
    
    /**
     * Insert News Data into DB
     *
     * @param int    publishdate
     * @param String newstext
     * @param int    senddate
     * @param String schoolYear
     *
     * @return int id;
     */
    public function InsertNewsIntoDB($publishdate, $newstext, $schoolyear) {
        return self::$connection->insertValues("INSERT into newsletter (`newsid`,`publish`,`text`,`schoolyear`,`lastchanged`)
			VALUES ('','$publishdate','$newstext','$schoolyear',CURRENT_TIMESTAMP)");
    }
    
    /**
     * Update News data
     *
     * @param int    id
     * @param int    publishdate
     * @param String newstext
     * @param int    senddate
     * @param String schoolYear
     */
    public function UpdateNewsInDB($id, $publishdate, $newstext, $schoolyear) {
        self::$connection->straightQuery("UPDATE newsletter SET publish=$publishdate,text='$newstext',
		schoolyear='$schoolyear', lastchanged=CURRENT_TIMESTAMP WHERE newsid='$id'");
    }
    
    /**
     * enter sent Date for Newsletter
     *
     * @param int id
     */
    public function enterNewsSentDate($id) {
        self::$connection->straightQuery("UPDATE newsletter SET sent=CURRENT_TIMESTAMP WHERE newsid='$id'");
    }
    
    /**
     * Get List of Newsletter recipients
     *
     * @return array(User)
     */
    public function getNewsRecipients() {
        $users = array();
        //get Teachers
        $data = self::$connection->selectValues("SELECT id,email,htmlnews FROM lehrer WHERE receive_news = 1");
        if ($data) {
            foreach ($data as $d) {
                $teacher = new Teacher($d[1], $d[0]);
                $teacher->setReceiveNewsMail(true);
                $d[2] ? $teacher->setHTMLNews(true) : $teacher->setHTMLNews(false);
                array_push($users, $teacher);
                unset($teacher);
            }
        }
        //get Parents
        $data = self::$connection->selectValues("SELECT DISTINCT userid,eltern.id,htmlnews,email FROM eltern,user,schueler
	WHERE userid = user.id
	AND eltern.id = schueler.eid
	AND receive_news = 1");
        if ($data) {
            foreach ($data as $d) {
                $parent = new Guardian($d[1], $d[3], $d[0]);
                $parent->setReceiveNewsMail(true);
                $d[2] ? $parent->setHTMLNews(true) : $parent->setHTMLNews(false);
                array_push($users, $parent);
                unset($parent);
            }
        }
        
        return $users;
    }
    
    
    /*
    * create HTML layouted Text of newsletter
    * @param Newsletter Object
    * @param User Object
    * @return String
    */
    public function makeHTMLNewsletter($newsletter, $user, $send = false) {
        $text = "";
        $linkStyle = 'style="font-family:Arial,Sans-Serif;font-size:10px;font-weight:bold;color: teal;font-decoration:underline;"';
        if (!$send) {
            ($user->getType() == 0) ? $imgsrc = "../assets/logo.png" : $imgsrc = "./assets/logo.png";
        } else {
            $imgsrc = "../assets/logo.png";
        }
        $text = mb_convert_encoding('<table border="0" cell-padding="0">
										<tr><td style="color:teal;font-family:Arial,Sans-Serif;font-weight:bold;font-size:18px;">Heinrich-Suso-Gymnasium Konstanz<hr style="color:teal;"></td></tr>
										<tr><td style="color:#666666;font-family:Arial,Sans-Serif;font-weight:bold;font-size:16px;">Newsletter vom ' .
            $newsletter->getNewsDate() . '<br></td></tr>', 'UTF-8');
        $text .= '<tr><td>';
        //Text auf Überschriften prüfen
        $newstext = mb_convert_encoding($newsletter->getNewsText(), 'UTF-8');
        $lines = explode("\r\n", $newstext);
        foreach ($lines as $line) {
            //Prüfe auf Überschrift2
            if (isset($line[0]) && $line[0] == "=" && $line[1] == "=") {
                $headerline = "";
                if ($line[2] == "=") {
                    //Header2
                    $offset = 3;
                    $style = 'style = "color:#008080; font-family:Arial,Sans-Serif;font-weight:bold; text-decoration: underline; font-size:13px;" ';
                } else {
                    //Header1
                    $offset = 2;
                    $style = 'style = "color: #008080; font-family:Arial,Sans-Serif;font-weight:bold; text-decoration: underline; font-size:16px;" ';
                }
                for ($x = $offset; $x < strlen($line) - $offset; $x++) {
                    $headerline .= $line[$x];
                }
                $text .= '<p ' . $style . '>' . $headerline . '</p>';
            } else {
                $text .= '<p style="color:#000000;font-size:12px">' . $line . '</p><br>';
            }
        }
        $text .= '</td></tr>';
        $text .= '<tr><td><hr style="color:teal;"></td></tr><tr><td style="color:#000000;font-size:10px">Diese Mail wurde automatisch versendet, bitte antworten Sie nicht auf diese Email!
		<br><b>Änderungen im Newsletterbezug über die </b><a ' . $linkStyle . ' href="' . 'https:\\www.suso.schulen.konstanz.de\intern' . '" target="_blank">Suso-Intern-App Webanwendung</a></td></tr>';
        $text .= '</table>';
        
        return $text;
    }
    
    /*
    * create Plain Text layouted Text of newsletter
    * @param Newsletter Object
    * @return String
    */
    public function makePlainTextNewsletter($newsletter) {
        $text = "";
        $text = "********************************\r\n" .
            "Heinrich-Suso-Gymnasium Konstanz \r\n" .
            "******************************** \r\n" .
            "Newsletter vom " . $newsletter->getNewsDate() . "\r\n";
        //Text auf Überschriften prüfen
        $newstext = mb_convert_encoding($newsletter->getNewsText(), 'UTF-8');
        $lines = explode("\r\n", $newstext);
        foreach ($lines as $line) {
            //Prüfe auf Überschrift2
            if (isset($line[0]) && $line[0] == "=" && $line[1] == "=") {
                $headerline = "";
                if ($line[2] == "=") {
                    //Header2
                    $offset = 3;
                    $space1 = "\r\n\r\n";
                    $space2 = "\r\n-----------------------------------------------\r\n\r\n";
                    
                } else {
                    //Header1
                    $offset = 2;
                    $space1 = "\r\n\r\n+++++++++++++++++++++++++++++++++++++++++++++++\r\n";
                    $space2 = "\r\n+++++++++++++++++++++++++++++++++++++++++++++++\r\n\r\n";
                }
                for ($x = $offset; $x < strlen($line) - $offset; $x++) {
                    $headerline .= $line[$x];
                }
                $text .= $space1 . $headerline . $space2;
            } else {
                $text .= $line;
            }
        }
        $text .= "\r\n\r\nDiese Mail wurde automatisch versendet, bitte antworten Sie nicht auf diese Email!";
        //Hinweis auf abbestellen
        $text .= "\r\nÄnderungen im Newsletterbezug über die Suso-Intern-App Webanwendung (https:\\www.suso.schulen.konstanz.de\intern)";
        
        return $text;
    }
    
    /*
	*APP only related functions
	*/
	
	/*
	*enter active appUser into DB
	* @param User Object
	*/
	public function enterAppUser($user){
		$userData = $this->getUserDataForAppMgt($user); 
		$userId= $userData[0];
		$userName = $userData[1];
		
		self::$connection->straightQuery("INSERT INTO appuser 
		(`auid`,`uid`,`user`,`logintime`)
		VALUES ('','$userId','$userName',CURRENT_TIMESTAMP)" );
		
		}
	/*
	*end active user's session
	* @param User Object
	*/
	public function endAppUserSession($user){
	$userData = $this->getUserDataForAppMgt($user); 
	$userId= $userData[0];
	$userName = $userData[1];
	self::$connection->straightQuery('DELETE FROM appuser WHERE uid='.$userId);
	
	}
	
	/*
	* get user data for app management - to be saved in DB
	* @param User Object
	* @return array
	*/
	private function getUserDataForAppMgt($user){
		//adapt if desired for different data, e.g. Email for parents etc.
		if ($user instanceof Teacher) {
			return array($user->getId(),$user->getFullName());	
		} elseif ($user instanceof StudentUser) {
			return array($user->getId(),$user->getFullName());
		} elseif  ($user instanceof Guardian) {
			return array($user->getId(),$user->getEmail());
		} elseif ($user instanceof Admin) {
			return array($user->getId(),$user->getEmail());
		}
	return false;
    }
    
    /**
     * writing logfiles for all kinds of purposes
     * @param string file
     * @param string line 
     */
    public function writeLog($file,$line){
        $fh = fopen($file,'a');
        $write = '[ '.date('Y-m-d H:i:s')." ] *** [action: ".$line."]\r\n";
        fputs($fh,$write);
        fclose($fh);
    }
}


?>
