<?php namespace administrator;

/**
 * The model class
 */
class Model extends \Model {
    
    /**
     * @var Model
     */
    protected static $model;
    
    /**
     *Konstruktor
     */
    protected function __construct() {
        parent::__construct();
        
    }
    
    static function getInstance() {
        return (self::$model == null || !(self::$model instanceof Model)) ? self::$model = new Model() : self::$model;
    }
    
    /**
     *read datafields from database
     *
     * @param bool $student
     *
     * @return array datafield names
     */
    public function readDBFields($student) {
        ($student) ? $table = "schueler" : $table = "lehrer";
        $data = self::$connection->selectFieldNames("SELECT * FROM " . $table);
        
        return $data;
        
    }
    
    /**
     *check if data exist in database
     *
     * @param int  $id
     * @param bool $student
     *
     * @return bool existence
     */
    public function checkDBData($student, $id) {
        ($student) ? $table = "schueler" : $table = "lehrer";
        $data = self::$connection->selectValues("SELECT id FROM $table where ASV_ID='$id' ");
        if (count($data) > 0) {
            return true;
        } else {
            return false;
        }
    }
    
    
    /**
     *update data
     *
     * @param bool
     * @param int
     * @param array
     */
    public function updateData($student, $id, $line) {
        ($student) ? $table = "schueler" : $table = "lehrer";
        $string = null;
        foreach ($line as $key => $value) {
            $key = self::$connection->escape_string($key);
            $value = self::$connection->escape_string($value);
            $key = trim($key);
            $value = trim($value);
            $value = addslashes($value);
            if (isset($string)) {
                $string = $string . ",$key='$value' ";
            } else {
                $string = "$key='$value' ";
            }
        }
        $string = $string . ",upd=1 WHERE ASV_ID='$id' ";
        $string = "UPDATE $table SET " . $string;
        
        //echo $string.'<br>';
        self::$connection->straightQuery($string);
    }
    
    /**
     *insert data
     *
     * @param bool
     * @param array
     */
    public function insertData($student, $line) {
        ($student) ? $table = "schueler" : $table = "lehrer";
        $fieldstring = null;
        $valuestring = null;
        foreach ($line as $key => $value) {
            $key = self::$connection->escape_string($key);
            $value = self::$connection->escape_string($value);
            $key = trim($key);
            $value = trim($value);
            $value = addslashes($value);
            if (isset($fieldstring)) {
                $fieldstring = $fieldstring . ",`$key`";
            } else {
                $fieldstring = "`$key`";
            }
            if (isset($valuestring)) {
                $valuestring = $valuestring . ",'$value'";
            } else {
                $valuestring = "'$value'";
            }
        }
        $fieldstring = $fieldstring . ",`upd`";
        $valuestring = $valuestring . ",'1'";
        $string = "INSERT INTO $table (" . $fieldstring . ") VALUES (" . $valuestring . ")";
        //echo $string.'<br>';
        self::$connection->insertValues($string);
        
    }
    
    /**
     * delete unused data from DB
     *
     * @param bool $student
     *
     * @return int amount of deletions
     */
    public function deleteDataFromDB($student) {
        $toDelete = 0;
        ($student) ? $table = "schueler" : $table = "lehrer";
        $data = self::$connection->selectValues("SELECT id FROM $table WHERE upd=0");
        $toDelete = count($data);
        self::$connection->straightQuery("DELETE FROM $table WHERE upd=0");
        
        return $toDelete;
    }
    
    /**
     *set update status to zero
     *
     * @param bool $student
     */
    public function setUpdateStatusZero($student) {
        ($student) ? $table = "schueler" : $table = "lehrer";
        self::$connection->straightQuery("UPDATE $table SET upd=0");
    }
    
    
    
    /**
     *
     * @return array(string) with form names
     */
    public function getForms() {
        $data = self::$connection->selectValues("SELECT DISTINCT klasse FROM schueler ORDER BY klasse"); // returns data[n][data]
        
        $forms = array();
        
        foreach ($data as $item) {
            array_push($forms, $item[0]);
        }
        
        return $forms;
        
    }
    
	
	/**
	* connect teachers to classes
	* @param array
	*/
	public function setLessons($lessons) {
	//truncate table
	self::$connection->straightQuery("TRUNCATE TABLE unterricht");
	foreach($lessons as $l) {
			$klasse = $l['class'];
			$lid = $this->getTeacherIdByShortName($l['teacher']);
			if (!$lid) {
				return $l['teacher'];
			}
			//enter data into DB
			self::$connection->insertValues("INSERT INTO unterricht (`id`,`lid`,`klasse`) VALUES ('','$lid','$klasse')");
			
		}
	return null;
	}
    
    /**
     * connect teacher with form
     * in individual mode
     * @param        array (int) with teacher Ids
     * @param string $form class
     */
    public function setTeacherToForm($teacher, $form) {
        $form = self::$connection->escape_string($form);
        
        $data = self::$connection->selectAssociativeValues("SELECT * FROM unterricht WHERE klasse='$form'");
        
        $dbIds = array();
        
        $new = array();
        $same = array();
        $deleted = array();
        
        if ($teacher == null) { // delete all teachers
            
            self::$connection->straightQuery("DELETE FROM unterricht WHERE klasse='$form'");
            
            return;
        } else
            if ($data != null) {
                foreach ($data as $value) {
                    $id = $value['lid'];
                    $dbIds[] = $id;
                }
                
                foreach ($teacher as $id) {
                    if (in_array($id, $dbIds))
                        $same[] = $id;
                    else
                        $new[] = $id;
                }
                
                foreach ($dbIds as $id) {
                    if (!in_array($id, array_merge($new, $same)))
                        $deleted[] = $id;
                }
            } else {
                $new = $teacher;
            }
        
        $query = "";
        
        foreach ($deleted as $dId)
            $query .= "DELETE FROM unterricht WHERE klasse='$form' AND lid=$dId; ";
        foreach ($new as $aId)
            $query .= "INSERT INTO unterricht(klasse, lid) VALUES('$form', $aId); ";
        
        if ($query != '')
            self::$connection->straightMultiQuery($query);
        
    }
    
    /**
     * get teachers in form
     *
     * @param string $form
     *
     * @return array[teacherId](array(form) )
     */
    public function getTeachersOfForm($form) {
        $form = self::$connection->escape_string($form);
        $teachersOfForm = array();
        $teachers = array();
        $tchrs = self::$connection->selectValues("SELECT lid FROM unterricht WHERE klasse='$form' ");
        if (count($tchrs) > 0) {
            foreach ($tchrs as $t) {
                $teachers[] = $t[0];
            }
        } else {
            $teachers = null;
        }
        
        $teachersOfForm[$form] = $teachers;
        unset($teachers);
        
        return $teachersOfForm;
    }
    
    
    /**
     *delete Slot from DB
     *
     * @param int slotId
     */
    public function deleteSlot($id) {
        self::$connection->straightQuery("DELETE FROM time_slot WHERE id='$id'");
    }
    
    /**
     *insert Slot into DB
     *
     * @param string $start
     * @param string $end
     */
    public function insertSlot($start, $end) {
        $start = $this->makeDateTime($start);
        $end = $this->makeDateTime($end);
        
        return self::$connection->insertValues("INSERT INTO time_slot (`id`,`anfang`,`ende`) VALUES ('','$start','$end') ");
    }
    
    
    /**
     * reset all bookable slots
     */
    public function clearBookableSlots() {
        self::$connection->straightQuery("TRUNCATE TABLE bookable_slot");
    }
    
    
    /**
     *create bookable appointments according to lessonVolume of teacher
     *hardCoded - to be adapted to option Data
     *
     * @param int slotId
     */
    public function createBookableSlots($slotId) {
        $FULL = 13;
        $data = self::$connection->selectValues("SELECT id FROM lehrer WHERE deputat>$FULL");
        foreach ($data as $d) {
            self::$connection->straightQuery("INSERT INTO bookable_slot (`id`,`slotid`,`lid`,`eid`) VALUES ('','$slotId','$d[0]',NULL)");
        }
    }
    
    /**
     *create DateTime Format
     *
     * @param string
     *
     * @return DateTime
     */
    private function makeDateTime($string) {
        $da = explode(" ", $string);
        $date = $da[0];
        $time = $da[1];
        $dateArr = explode(".", $date);
        $newDate = $dateArr[2] . "-" . $dateArr[1] . "-" . $dateArr[0] . " " . $time;
        
        return $newDate;
    }
    
    
    /**
     *returns options for admin purpose
     *
     * @return array(string)
     */
    public function getOptionsForAdmin() {
        $options = array();
        $data = self::$connection->selectValues("SELECT kommentar, type, value, field FROM options ORDER BY ordinal");
        if (isset($data)) {
            foreach ($data as $d) {
                $options[] = array("kommentar" => $d[0], "type" => $d[1], "value" => $d[2], "field" => $d[3]);
            }
        }
        
        return $options;
    }
    
    /**
     *updates options after changes
     *
     * @param array POST
     */
    public function updateOptions($data) {
        foreach ($data as $key => $value) {
            $key = self::$connection->escape_string($key);
            $value = trim(self::$connection->escape_string($value)," ");
            self::$connection->straightQuery("UPDATE options SET value ='$value' WHERE type='$key' ");
        }
    }
    
    /**
     *Eintrag aller Termine in die Datenbank
     *
     * @param $termine Array(Terminobjekt)
     */
    public function addEventsToDB($termine) {
        //Tabelle leeren
        self::$connection->straightQuery("TRUNCATE termine");
        foreach ($termine as $t) {
            $query = "INSERT INTO termine (`tNr`,`typ`,`start`,`ende`,`staff`) VALUES ('','$t->typ','$t->start','$t->ende','$t->staff')	";
            self::$connection->insertValues($query);
        }
    }
    
    /**
     * get values from ini-file
     *
     * @return string
     */
    public function getIniParams() {
        return self::$connection->getIniParams();
    }
    
    /**
     * Returns all users which email are starting with $startingWith
     *
     * @param string $startingWith
     *
     * @return array
     */
    public function getUsers($startingWith) {
        $arr = array();
        $startingWith = self::$connection->escape_string($startingWith);
        $data = self::$connection->selectAssociativeValues("SELECT email FROM user WHERE email LIKE '%$startingWith%'");
        if ($data != null && !empty($data))
            foreach ($data as $d)
                array_push($arr, $d['email']);
        
        return $arr;
    }
	
	/**
	* returns all pupils whose surname starts with $startingWith
	* @param string 
	* @param string
	* @return array
	*/
	public function getPupils($startingWith) {
	$arr = array();
	
        $startingWith = self::$connection->escape_string($startingWith);
        $query = "SELECT * FROM schueler WHERE name LIKE '$startingWith%'"; 
		$data = self::$connection->selectAssociativeValues($query);
        if ($data != null && !empty($data))
            foreach ($data as $d) {
				$locker = array();
				$lckrQueryData = self::$connection->selectValues("SELECT nr,location,hiredate FROM lockers WHERE hired = " . $d['id']);
				if ( !empty($lckrQueryData) ) {
					$locker = array("id" => $lckrQueryData[0][0], "location" => $lckrQueryData[0][1] , "hiredate" => $lckrQueryData[0][2] );
					} 
				if (isset($d['eid'])) {
				//get parent name
				$parent = \Model::getParentUserByParentId($d['eid']);
				$parentFullname = $parent['fullname'];
				$parentEmail = $parent['email'];
				//Debug::writeDebugLog(__method__,$parent->getFullname() );
				if(isset($d['eid2'])) {
					//get parent2 name
					$parent2 = \Model::getParentUserByParentId($d['eid2']);
					$parent2Fullname = $parent2['fullname'];
					$parent2Email = $parent2['email'];
					} else {
					$parent2Fullname = $parent2Email = null;
					}
					
				} else {
				$parentFullname = $parentEmail = null;
				$eid2 = $parent2Fullname = $parent2Email = null;
				}
                $arr[] = array("id"=>$d['id'],"asvId"=>$d['ASV_ID'],"dob"=>$d['gebdatum'],
				"name"=>$d['name'],"vorname"=>$d['vorname'],"klasse"=>$d['klasse'],
				"eid"=>$d['eid'],"parent"=>$parentFullname,"mail"=>$parentEmail, 
				"eid2" => $d['eid2'],"parent2" => $parent2Fullname, "mail2" => $parent2Email, "locker" => $locker ); 
				unset($locker);
			}
        return $arr;	
	}
	
	
	
	/**
	* returns all users without registered children
	* @return array()
	*/
	public function getUsersWithoutKids() {
		$users = array();
		$data = self::$connection->selectValues("SELECT email,name,vorname,registered,user.id,eltern.id 
		FROM user,eltern 
		WHERE user.id = eltern.userid order by registered");
		if ($data) {
		foreach ($data as $d) {
				$users[] = array("name"=>$d[1],"vorname"=>$d[2],"mail"=>$d[0],
				"registered"=>$d[3],"id"=>$d[4],"eid"=>$d[5]);
				}		
			}

		$unused = array();
		foreach ($users as $u) {
			$data = self::$connection->selectValues("SELECT id FROM schueler WHERE eid = ".$u['eid']);
			if (!$data[0][0] ) {
				array_push($unused,$u);
				}
			}
	
		return $unused;
	}
	
	/**
	* delete Parent User
	* used when no kids have been assigned
	* @param int
	*/
	public function deleteParentUser($id) {
		$data = self::$connection->selectValues("SELECT id,name,vorname from eltern WHERE userid = $id");
		$parent = $data[0][0];
		self::$connection->straightQuery("DELETE FROM eltern where id = $parent");
		self::$connection->straightQuery("DELETE FROM user where id = $id");
		return array("name"=>$data[0][1],"vorname"=>$data[0][2]);
	}


	/**
	* get rergistration requests
	* @return array
	*/
	public function getRegistrationRequests(){
		$requests = null;
		$data = self::$connection->selectAssociativeValues("SELECT * FROM registration_request ORDER BY request_date");
		if ($data) {
			foreach ($data as $d) {
			//get Parent Data
			//$parent = $this->getUserById($d['eid'],array("user_type"=>1,"email"=>$d['email'],"id"=>$d['eid']) ) ;
			$parent = $this->getParentByParentId($d['eid']);

			
			$requests[] = array("id"=>$d['requestId'],
			"parentName"=>$parent->getFullname(),
			"name"=>$d['name'],
			"klasse"=>$d['klasse'],
			"dob"=>$d['dob'],
			"email"=>$d['email'],
			"requestDate"=>$d['request_date']);
			}
		}
	return $requests;	
	}
	
	/**
	* finish Registration Request
	* @param int requestId
	* @param pupilId
	*/
	public function finishRegistrationRequest($requestId, $pupilId) {
	$registrationData = array();
	$data = self::$connection->selectAssociativeValues("SELECT * from registration_request WHERE requestId = $requestId");
	if ($data) {
		$data = $data[0];
		\Debug::writeDebugLog(__method__,"ermittelte Email für Anfrage ".$requestId." : ".$data['email']);
		//get pupil's registration data
		$pdata = self::$connection->selectAssociativeValues("SELECT * FROM schueler WHERE id = ". $pupilId);
		if($pdata) {
			$pdata = $pdata[0];
			$registrationData = array("email"=>$data['email'],
									"name"=>$data['name'],
									"date"=>$data['request_date'],
									"asvId"=>$pdata['ASV_ID'] );
			return $registrationData;
			}
		} else {
			return false;
		}

	 	
	}
	
	/**
	* delete a registration requestDate
	* @param int id
	*/
	public function deleteRegistrationRequest($id) {
	self::$connection->straightQuery("DELETE FROM registration_request WHERE requestId = $id");	
	}
	
	/**
	* get absences for pdf print
	* @return array
	*/
	public function getLastDaysAbsences(){
	$lastday = 3;
	$maxdate = date(Y-m-d) - $lastday;
	//self::$connection->getAssociativeValues();
	$absentees = array();
	return $absentees;
	}
	
	
	
   /**
   * get all hired out lockers
   * @return JSON 
   */
   public function getLockerList() {
   $hiredOut = array();
   $empty = array();
   $data = self::$connection->selectValues("SELECT nr, hired, location FROM lockers ORDER BY nr");
   if (!empty($data) ){
		foreach($data as $d) {
			if ($d[1] !== null) {
				//locker is hired out
				$student = \Model::getStudentById($d[1]);
				//$studentData = array("id" => $student->getId(), "fullname" => $student->getFullName(), "klasse" => $student->getClass() );
				$locker = array("locker" => $d[0], "location" => $d[2], "student" => $student);
				array_push($hiredOut,$locker);
				} else {
				$emptyLocker = array("locker" =>$d[0], "location" => $d[2]);
				array_push($empty, $emptyLocker);
				}
			}
		}
	$lockers = array("hired" => $hiredOut, "empty" => $empty);
   return $lockers;
   }
   

   /**
   * hire a locker to a student
   * @param int locker id
   * @param int student id
   * @return boolean
   */
   public function hireoutLocker($locker,$student){
	$time = date('d.m.Y H:i');
	\Debug::writeDebugLog(__method__,'UPDATE lockers SET hired ="' . $student . '",hiredate = "' . $time .'" WHERE nr=' . $locker);
	$result = self::$connection->straightQuery('UPDATE lockers SET hired ="' . $student . '",hiredate = "' . $time .'" WHERE nr=' . $locker);
   
	}
	

	/** 
	*return a locker
	* @param int id
	*/
	public function returnLocker($locker) {
		self::$connection->straightQuery('UPDATE lockers SET hired = null, hiredate = null WHERE nr=' . $locker);
	}
}
?>
