<?php

    /**
     * The model class
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
         * @return string
         */
        public function getIniParams() {
            return self::$connection->getIniParams();
        }


        /**
         * @param string $vorname Schueler Vorname
         * @param string $name Schueler Nachname
         * @return Student
         **/
        public function getStudentByName($name, $surname = null) {

            $name = self::$connection->escape_string($name);
            if ($surname != null) {
                $surname = self::$connection->escape_string($surname);
                $wholeName = str_replace(' ', '', $name . $surname);
            } else {
                $wholeName = $name;
            }

            $data = self::$connection->selectAssociativeValues("SELECT * FROM schueler WHERE Replace(CONCAT(vorname, name), ' ', '') = '$wholeName'");

            if ($data == null)
                return null;

            $data = $data[0];

            return new Student($data['id'], $data['klasse'], $data['name'], $data['vorname'], $data['gebdatum'], $data['eid']);
        }

        /**
         * @param $uid int
         * @return User | Teacher | Admin | Guardian
         */
        public function getUserById($uid, $data = null) {

            if ($data == null)
                $data = self::$connection->selectAssociativeValues("SELECT * FROM user WHERE id=$uid");
            if ($data == null)
                return null;
            if (isset($data[0]))
                $data = $data[0];

            $type = $data['user_type'];

            switch ($type) {
                case 0: // Admin
                    return new Admin($data['id'], $data['email']);
                    break;
                case 1: // Parent / Guardian
                    $data2 = self::$connection->selectAssociativeValues("SELECT * FROM eltern WHERE userid=$uid")[0];

                    return new Guardian($data['id'], $data['email'], $data2['id'], $data2['name'], $data2['vorname']);
                case 2:
                    // non-existend
                    die("Why are we here?!");
                default:
                    return null;
                    break;
            }

        }

        /**
         * @param string $email the user email
         * @return User user
         */
        public function getUserByMail($email) {
            $email = self::$connection->escape_string($email);
            $data = self::$connection->selectAssociativeValues("SELECT * FROM user WHERE email='$email'");

            if ($data == null)
                return null;

            return $this->getUserById($data[0]['id'], $data);
        }

        /**
         * @param int $tchrId
         * @return array[String => String]
         */
        public function getTeacherNameByTeacherId($teacherId, $data = null) {
            if ($data == null)
                $data = self::$connection->selectAssociativeValues("SELECT * FROM lehrer WHERE lehrer.id=$teacherId");

            if (isset($data[0]))
                $data = $data[0];

            $surname = isset($data["name"]) ? $data["name"] : null;
            $name = isset($data["vorname"]) ? $data["vorname"] : null;

            return array("name" => $name, "surname" => $surname);
        }


        /**
         * @param int $usrId UserId
         * @return array[Student] array[childrenId]
         */
        public function getChildrenByParentUserId($usrId, $limit = null) {
            if (isset($limit)) {
                $query = "SELECT schueler.* FROM schueler, eltern WHERE schueler.eid=eltern.id AND eltern.userid=$usrId AND schueler.klasse < $limit"; //a bit crude, isn't it
            } else {
                $query = "SELECT schueler.* FROM schueler, eltern WHERE schueler.eid=eltern.id AND eltern.userid=$usrId";
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
         * @param $studentId int
         * @return Student
         */
        public function getStudentById($studentId) {
            $data = self::$connection->selectAssociativeValues("SELECT * FROM schueler WHERE id=$studentId");


            if ($data == null)
                return null;

            $data = $data[0];

            return new Student($data['id'], $data['klasse'], $data['name'], $data['vorname'], $data['gebdatum'], $data['eid']);
        }

        /**
         * @param string $class
         * @return array[Teacher] array with teacherIds
         */
        public function getTeachersByClass($class) {
            $class = self::$connection->escape_string($class);
            $data = self::$connection->selectValues("SELECT lehrer.id FROM lehrer, unterricht WHERE unterricht.klasse='$class' AND unterricht.lid=lehrer.id"); // returns data[n][data]

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
         * @param $tchrId int teacherId
         * @return Teacher
         */
        public function getTeacherByTeacherId($tchrId, $data = null) {
            if ($data == null)
                $data = self::$connection->selectAssociativeValues("SELECT * FROM lehrer WHERE id=$tchrId");

            if (isset($data[0]))
                $data = $data[0];


            if ($data == null)
                return null;

            return new Teacher($data['email'], $data['id'], $data);
        }

        /**
         * @param $teacherId
         * @param $rawData
         * @return string
         */
        public function getTeacherLdapNameByTeacherId($teacherId, $rawData = null) {
            if ($rawData == null)
                $rawData = self::$connection->selectAssociativeValues("SELECT ldapname FROM lehrer WHERE id=$teacherId");

            if ($rawData == null)
                return null; // empty / not found

            if (isset($rawData[0]))
                $rawData = $rawData[0];

            return $rawData;
        }
		
		/**
         * @param $teacherId
         * @param $rawData
         * @return string
         */
        public function getTeacherUntisNameByTeacherId($teacherId, $rawData = null) {
			$returnData = null;
            if ($rawData == null) {
				$data = self::$connection->selectValues("SELECT untisname FROM lehrer WHERE id=$teacherId");
				if ($data == null){
					$returnData = null; // empty / not found
					} 
				else {
					$returnData = $data[0][0];
					}
				}
            if (isset($rawData["untisName"])) {
						$returnData = $rawData["untisName"];
						} 
						
            return $returnData;
        }
		
		/**
         * @param $teacherId
         * @param $rawData
         * @return string
         */
        public function getTeacherShortNameByTeacherId($teacherId, $rawData = null) {
			$returnData = null;
            if ($rawData == null) {
				$data = self::$connection->selectValues("SELECT kuerzel FROM lehrer WHERE id=$teacherId");
				if ($data == null) {
					$returnData = null; // empty / not found
					}
				else {
					$returnData = $data[0][0];
					}
				}
            if (isset($rawData["shortName"]))
                $returnData = $rawData["shortName"];

            return $returnData;
        }

        /**
         * @param $teacherId int
         * @param $rawData
         * @return int
         */
        public function getTeacherLessonAmountByTeacherId($teacherId, $rawData = null) {
            $data = self::$connection->selectValues("SELECT deputat FROM lehrer WHERE id=$teacherId");
            if (isset($data)) {
                $lessons = $data[0][0];
            }

            return $lessons;
        }

        /**
         * @param $email
         * @param $pwd
         * @return Teacher | null
         */
        public function getTeacherByEmailAndLdapPwd($email, $pwd) {

            $data = self::$connection->selectAssociativeValues("SELECT * FROM lehrer WHERE email='$email'");

            if (isset($data[0]))
                $data = $data[0];

            if ($data == null)
                return null;

            $tId = $data['id'];
            $ldapName = $this->getTeacherLdapNameByTeacherId($tId, $data);

            if ($ldapName == null)
                die("LDAP name not set for $email! If you are 1000% sure this is your real suso email, please contact you system admin of choice."); // rip

            return $this->getTeacherByLdapNameAndPwd($ldapName, $pwd);
        }


        /**
         * @param $ldapName
         * @param $pwd
         * @param $data
         * @return null|Teacher
         */
        public function getTeacherByLdapNameAndPwd($ldapName, $pwd, $data = null) {
            if ($data == null) {
                $data = self::$connection->selectAssociativeValues("SELECT * FROM lehrer WHERE ldapname='$ldapName'");


                $novelData = $this->checkNovellLogin($ldapName, $pwd);

                if (!isset($novelData->{'code'}) || !isset($novelData->{'type'}) || $novelData->{'code'} != "200" || $novelData->{'type'} != 'Teacher') {
                    ChromePhp::info(json_encode($novelData));
                    if (isset($novelData->{'type'}) && $novelData->{'type'} == "student")
                        die("Schüler werden noch nicht unterstützt.");
                    else
                        return null;
                }
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
         * @return bool
         */
        private function checkAssignedSlot($slotId, $teacherId) {
            $data = self::$connection->selectvalues("SELECT slotid FROM bookable_slot WHERE slotid=$slotId AND lid=$teacherId");
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
            self::$connection->straightQuery("DELETE FROM bookable_slot WHERE slotid=$slotId AND lid=$teacherId");
        }


        /**
         *returns assigned slots of a teacher
         *
         * @param int teacherId
         * @returns array(int)
         */
        public function getAssignedSlots($teacher) {
            $slots = array();
            $data = self::$connection->selectValues("SELECT slotid FROM bookable_slot WHERE lid=$teacher");
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
            $data = self::$connection->selectAssociativeValues("SELECT userid FROM eltern WHERE id=$eid");
            if ($data == null)
                return null;
            $data = $data[0];

            return $this->getUserById($data['userid']);
        }

        /**
         * @param int $slotId
         * @param int $userId
         * @param int $teacherId
         * @return int appointmentId
         */
        public function bookingAdd($slotId, $userId) {
            return self::$connection->insertValues("UPDATE bookable_slot SET eid=$userId WHERE id=$slotId");
        }

        /**
         * @param int $appointment
         */
        public function bookingDelete($appointment) {
            self::$connection->straightQuery("UPDATE bookable_slot SET eid=NULL WHERE id=$appointment");
        }

        /**
         * @param $parentId int
         * @param $appointment int
         * @return boolean
         */
        public function parentOwnsAppointment($parentId, $appointment) {
            $data = self::$connection->selectAssociativeValues("SELECT * FROM bookable_slot WHERE id=$appointment");
            if (isset($data[0]))
                $data = $data[0];
            if (!isset($data) || $data['eid'] == null)
                return true; //throw exception?
            return $data['eid'] == $parentId;
        }

        /**
         * @param $slotId int
         * @param $userId int
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
         * @return array
         */
        public function getAllBookableSlotsForParent($teacherId, $parentId) {
            $slots = array();
            $data = self::$connection->selectValues("SELECT bookable_slot.id,anfang,ende,eid,time_slot.id FROM bookable_slot,time_slot 
			WHERE lid=$teacherId
			AND bookable_slot.slotid=time_slot.id
			AND (eid IS NULL OR eid=$parentId)
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
         * @return array(slotId, bookingId, teacherId)
         */
        public function getAppointmentsOfParent($parentId) {
            $appointments = array();
            $data = self::$connection->selectValues("SELECT time_slot.id,bookable_slot.id,bookable_slot.lid FROM time_slot,bookable_slot
			WHERE time_slot.id=bookable_slot.slotid
			AND bookable_slot.eid=$parentId ORDER BY anfang");
            if (isset($data)) {
                foreach ($data as $d) {
                    $appointments[] = array("slotId" => $d[0], "bookingId" => $d[1], "teacherId" => $d[2]);
                }
            }

            return $appointments;
        }

        /**
         * returns taught classes of teacher
         *
         * @param int teacherId
         * @return array(string)
         */
        public function getTaughtClasses($teacherId) {
            $data = self::$connection->selectValues("SELECT klasse FROM unterricht WHERE lid = $teacherId ORDER BY klasse");
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
         * @return array(slotId, bookingId, Guardian)
         */
        public function getAppointmentsOfTeacher($teacherId) {
            $appointments = array();
            $data = self::$connection->selectValues("SELECT time_slot.id,bookable_slot.id,bookable_slot.eid,eltern.userid,eltern.name,eltern.vorname,user.email
			FROM time_slot,bookable_slot,eltern,user
			WHERE time_slot.id=bookable_slot.slotid
			AND bookable_slot.eid=eltern.id
			AND eltern.userid=user.id
			AND bookable_slot.lid=$teacherId ORDER BY anfang");
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
         * @return array("anfang","ende","teacher");
         */
        public function getBookingDetails($parentId) {
            $bookingDetails = array();
            $data = self::$connection->selectValues("SELECT anfang,ende,lid 
		FROM bookable_slot,time_slot
		WHERE bookable_slot.slotid = time_slot.id
		AND eid = $parentId
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
         * @param $email
         * @param $password
         * @return bool user exists in database and password is equal with the one in the database
         */
        public function passwordValidate($email, $password) {

            $email = self::$connection->escape_string($email);
            //$password = self::$connection->escape_string($userName);

            $data = self::$connection->selectAssociativeValues("SELECT password_hash from user WHERE email='$email'");

            if ($data == null)
                return false;


            $data = $data[0];

            $pwd_hash = $data['password_hash'];


            return password_verify($password, $pwd_hash);
        }


        /**
         * @param $pid array or int parents children ids (array[int] || int)
         * @param $email string parents email
         * @param $pwd string parents password
         * @param $name string parent name
         * @param $surname string parent surname
         * @return array newly created ids of parent (userid and parentid)
         */
        public function registerParent($email, $pwd, $name, $surname) {

            $email = self::$connection->escape_string($email);
            $pwd = password_hash($pwd, PASSWORD_DEFAULT);

            $query = "INSERT INTO user (user_type, password_hash, email) VALUES (1,'$pwd', '$email');";

            //Create parent in database and return eid
            $usrId = self::$connection->insertValues($query);

            $parentId = self::$connection->insertValues("INSERT INTO eltern (userid, vorname, name) VALUES ($usrId, '$name', '$surname');");

            //return eid
            return array("uid" => $usrId, "pid" => $parentId);

        }

        /**
         * Adds new student as child to parent
         *
         * @param $parentId int Parent ID
         * @param $studentIds array Student ID
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

                $query = "UPDATE schueler SET eid=$parentId WHERE id=$id;";
            }
            self::$connection->straightQuery($query);

            return true;
        }

        /**
         * @param $usr string novell user
         * @param $pwd string novell passwd
         * @returns array(string) [user => username case sensitive, type => student / teacher [, class => if student: students class]]
         * @throws Exception when error was thrown while connection to remote server or response was empty
         */
        public
        function checkNovellLogin($usr, $pwd) {

            $apiUrl = "https://intranet.suso.schulen.konstanz.de/gpuntis/est.php"; //TODO: do by config or sth
            $headers = array('Authorization: Basic ' . base64_encode("$usr:$pwd"));

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiUrl);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
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
         *Termine aus Datenbank auslesen
         *
         * @param $includeStaff Boolean
         * @return Array(Terminobjekt)
         */
        public function getEvents($isTeacher = null) {
            isset($isTeacher) ? $query = "SELECT typ,start,ende,staff FROM termine ORDER BY start" : $query = "SELECT typ,start,ende,staff FROM termine WHERE staff=0 ORDER BY start";
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
		*@param $staff boolean 
		*@return Array(Terminobjekte)
		*/
		public function getNextDates($staff){
			$staff ? $query="SELECT typ,start,ende,staff FROM termine ORDER BY start" : $query="SELECT typ,start,ende,staff FROM termine WHERE staff=0 ORDER BY start" ;
			$data=self::$connection->selectValues($query);
			$x=0;
			foreach ($data as $d){
				$termin=new Termin();
				$termine[$x]=$termin->createFromDB($d);
				$x++;
				}
				
			//Ermittle die neuesten Termine
			$today=date('d.m.Y');
			$added = strtotime("+21 day", strtotime($today)); 
			$limit= date("d.m.Y", $added); 
			$todayTimestamp = strtotime($today);
			$limitTimestamp = strtotime($limit);
			
			$nextDates=array();
			$x=0;
			foreach ($termine as $t){
				if(strtotime($t->sday)>=$todayTimestamp  && strtotime($t->sday)<=$limitTimestamp) {
					$nextDates[$x]=$t;
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
            self::$connection->straightQuery("UPDATE user SET password_hash='$pwdhash' WHERE id=$usrId");
        }

        /** Change userdata
         *
         * @param $usrId
         * @param $name
         * @param $surname
         * @param $email
         * @return bool success
         */
        public function updateUserData($usrId, $name, $surname, $email) {

            $check = self::$connection->selectValues("SELECT * FROM `user` WHERE email='$email' AND NOT id = $usrId");

            if(isset($check[0]))
                return false;

            self::$connection->straightMultiQuery("UPDATE user SET email='$email' WHERE id=$usrId; UPDATE eltern SET vorname='$name', name='$surname' WHERE userid=$usrId");

            return true;
        }
		
		
		/*************************************************
		********methods only used in CoverLesson module***
		*************************************************/
		
		/**
		*get all relevant days for display
		*@param bool $isTeacher
		*/
		public function VP_getAllDays($isTeacher){
		(!$isTeacher) ? $add = " AND tag<3 " : $add="";
		$allDays = array();
		$data = self::$connection->selectValues("SELECT DISTINCT datum FROM vp_vpdata WHERE tag>0 ".$add." order by datum ASC");
		if(isset($data)) {
			foreach($data as $d){
			$allDays[] = array("timestamp"=>$d[0],"dateAsString"=>$this->getDateString($d[0]));
			}
		}
		return $allDays;
		}
		
		/**
		*returns a date in format "<Weekday> DD.MM.YYYY "
		*@param string $date "YYYYMMDD"
		*@return string
		*/
		private function getDateString($date){
			return $this->getWeekday($date).". ".$this->reverseDate($date);	
		}
		
		/**
		*returns day of the week for a given date
		*@param String "YYYMMDD"
		*@return String
		*/
		private function getWeekday($date){
			$wochentage = array ('So','Mo','Di','Mi','Do','Fr','Sa');
			$monat=$date[4].$date[5];
			$tag=$date[6].$date[7];
			$jahr=$date[0].$date[1].$date[2].$date[3];
			$date = getdate(mktime ( 0,0,0, $monat, $tag, $jahr));
			$wochentag = $date['wday'];
			return $wochentage[$wochentag];
			}	
			
		
		/**
		*return date in format DD.MM.YYYY
		*@param string $date in format "YYYYMMDD"
		*@return String
		*/
		private function reverseDate($date){
			return $date[6].$date[7].".".$date[4].$date[5].".".$date[0].$date[1].$date[2].$date[3];
			}
		
		
		/*
		*return date in Format Monday, den dd.mm.YYYY
		* @param $date String im Format YYYYMMDD
		* @return String
		*/
		public function makeCompleteDate($date){
		$year = $date[0].$date[1].$date[2].$date[3];
		$month = $date[4].$date[5];
		$day = $date[6].$date[7];
		$wochentage = array ('Sonntag','Montag','Dienstag','Mittwoch','Donnerstag','Freitag','Samstag');
		$datum = getdate(mktime ( 0,0,0, $month, $day, $year));
		$wochentag = $datum['wday'];
		$completeDate = $wochentage[$wochentag].", den ".$day.".".$month.".".$year;
		return $completeDate;
		}
		
		
		/**
		*returns date of last update
		*@return String timestamp
		*/
		public function getUpdateTime(){
		$data=self::$connection->selectValues("SELECT DISTINCT stand FROM vp_vpdata WHERE tag=1");
		if(count($data)>0){
			return $data[0][0];
			}
		else{
			return null;
			}
		}
		
		
		/**
		*get all current cover lessons for teachers
		*@param boolean $showAll all coverLessons or only those of current user
		*@param Teacher Object
		*@param array("datumstring","timestamp") $allDays
		*@return Array(coverLesson Object)
		*/
		public function getAllCoverLessons($showAll,$tchr,$allDays){
		$vertretungen=null;
		$untisName = $tchr->getUntisName();
		$shortName = $tchr->getShortName(); 
		(!$showAll)? $add=" AND (vLehrer=\"$untisName\" OR eLehrer=\"$shortName\") " : $add="";
		foreach($allDays as $day){
			$datum = $day['timestamp'];
			$data=self::$connection->selectAssociativeValues("SELECT * FROM vp_vpdata 
			WHERE tag>0
			AND aktiv=true
			AND datum=\"$datum\"
			$add
			ORDER BY datum,vLehrer,stunde ASC");

			if(count($data)>0){
				foreach($data as $d){
					$coverLesson=new CoverLesson();
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
		* get all Cover Lessons for a teacher in Untis presented data (i.e. "--" and "selbst" will be shown)
		* no CoverLesson Object will be instantiated
		* @param Teacher Object
		* @return array()
		*/
		public function getCoverLessonsByTeacher($teacher){
		$coverLessons = array();
		$tname=$teacher->getUntisName();
		$tkurz=$teacher->getShortName();
		$data=self::$connection->selectValues("SELECT datum,klassen,stunde,fach,raum,eLehrer,eFach,kommentar,vnr,vLehrer
		FROM vp_vpdata 
		WHERE (vLehrer=\"$tname\" OR eLehrer=\"$tkurz\" )
		AND aktiv=true and tag>0 ORDER by datum,stunde");
		//echo " --- Anzahl Vertretungen: ".count($data).'<br>';
		if(count($data)>0){
		foreach($data as $d) {
			$datum=$this->makeCompleteDate($d[0]);	
			$coverLessons[]=array("vnr"=>$d[8],"Datum"=>$datum,"Vertreter"=>$d[9],"Klassen"=>$d[1],"Stunde"=>$d[2],"Fach"=>$d[3],"Raum"=>$d[4],"statt_Lehrer"=>$d[5],"statt_Fach"=>$d[6],"Kommentar"=>$d[7]);
			}
		}
		return $coverLessons;
		}
		
		/**
		*get CoverLesson data by primary key
		* @param int id
		* @return array()
		*/
		public function getCoverLessonById($id){
		$coverLesson = null;
		$data=self::$connection->selectAssociativeValues("SELECT * FROM vp_vpdata 
			WHERE vNr = $id");
		if (isset($data)) {
				$coverLesson = $data[0];
		}
		return $coverLesson;	
		}
		
		
		/*
		*get all cover lessons for parents
		*@param form array(String) $classes
		*@param array("datumstring","timestamp") $allDays
		*@return Array(coverLesson Object)
		*/
		public function getAllCoverLessonsParents($classes,$allDays){
		$vertretungen=null;
		//create query string to identify forms
		$classQuery = null;
		$x = 0;
		foreach ($classes as $class){
			if ($x == 0) {$classQuery = " AND (klassen LIKE ".'"%'.$class.'%"';}
			else {$classQuery .= " OR klassen LIKE ".'"%'.$class.'%"';}
			$x++;
		}
		$classQuery .= ")";
		foreach($allDays as $day){
			$datum=$day['timestamp'];
			$data=self::$connection->selectAssociativeValues("SELECT * FROM vp_vpdata 
			WHERE tag>0
			AND tag<3
			AND aktiv=true
			AND datum=\"$datum\"
			$classQuery
			ORDER BY datum,stunde ASC");
			if(count($data)>0){
				$x=0;
				foreach($data as $d){
					$coverLesson=new CoverLesson($this->connection);
					$coverLesson->constructFromDB($d);
					$vertretungen[$day["timestamp"]][]=$coverLesson;
					unset($coverLesson);
					}
				}
			unset($data);
			}
		return $vertretungen;
		}
		
		/*
		*get all cover lessons for students
		* @param Student
		* @param array("datumstring","timestamp") $allDays
		* @return Array(coverLesson Object)
		*/
		public function getAllCoverLessonsStudents($student,$allDays){
		$vertretungen=null;
		//create query string to identify forms
		$classQuery = null;
		$x = 0;
		foreach ($classes as $class){
			if ($x == 0) {$classQuery = " AND (klassen LIKE ".'"%'.$class.'%"';}
			else {$classQuery .= " OR klassen LIKE ".'"%'.$class.'%"';}
			$x++;
		}
		$classQuery .= ")";
		foreach($allDays as $day){
			$datum=$day['timestamp'];
			$data=self::$connection->selectAssociativeValues("SELECT * FROM vp_vpdata 
			WHERE tag>0
			AND tag<3
			AND aktiv=true
			AND datum=\"$datum\"
			$classQuery
			ORDER BY datum,stunde ASC");
			if(count($data)>0){
				$x=0;
				foreach($data as $d){
					$coverLesson=new CoverLesson($this->connection);
					$coverLesson->constructFromDB($d);
					$vertretungen[$day["timestamp"]][]=$coverLesson;
					unset($coverLesson);
					}
				}
			unset($data);
			}
		return $vertretungen;
		}
		
		
		
		/**
		*ermittle alle blockierten Räume
		*@param $datum array QueryResult 
		* @return array(String,String)
		*/

		public function getBlockedRooms($datum){
			$roomstring = "";
			$blockedRooms = array();
			foreach($datum as $d){
				$dtm = $d['timestamp'];
				$brs = self::$connection->selectValues("SELECT name FROM vp_blockierteraeume WHERE datum=\"$dtm\" ");
				if(isset($brs)) {
					foreach($brs as $room){
					if($roomstring == "") {$roomstring = $room[0];} else {$roomstring = $roomstring.", ".$room[0];}
					}
				}
				if($roomstring == "") {$roomstring = "keine";}
				$roomstring = wordwrap( $roomstring, 100, "<br />\n" );
				$blockedRooms[$d['timestamp']] = $roomstring;
			$roomstring = "";	
			}
			return $blockedRooms;
		}


		/**
		*ermittle alle abwesenden Lehrer
		*@param $datum array QueryResult
		* @return array(String,String) 
		*/

		public function getAbsentTeachers($datum){
			$atstring = "";
			$absentTeachers=array();
			foreach($datum as $d){
				$dtm = $d['timestamp'];
				$ats=self::$connection->selectValues("SELECT name FROM vp_abwesendeLehrer WHERE datum=\"$dtm\" ");
				if(isset($ats)) {
					foreach($ats as $t){
					if($atstring=="") {$atstring=$t[0];} else {$atstring=$atstring.", ".$t[0];}
					}
				}
				if($atstring=="") {$atstring="keine";}
				$atstring = wordwrap( $atstring, 150, "<br />\n" );
				$absentTeachers[ $d['timestamp'] ]=$atstring;
			$atstring="";	
			}
			return $absentTeachers;
		}
		
		/*
		*get Primary key and email by Teacher untisname
		* @param String $untisname
		* @return array(String, Int)
		*/
		public function getTeacherDataByUntisName($untisName){
		$tchrData = array();
		$data = self::$connection->selectValues("SELECT email,id FROM lehrer WHERE untisName=\"$untisName\" ");
		if(count($data)>0){
			$tchrData = array("email" => $data[0][0], "id" => $data[0][1]);
			return $tchrData;
			} else {
			return null;	
			}
		
		}
		
		/*
		*get Primary key and email by Teacher untisname
		* @param String $untisname
		* @return array(String, Int)
		*/
		public function getTeacherDataByShortName($short){
		$tchrData = array();
		$data = self::$connection->selectValues("SELECT email,id FROM lehrer WHERE kuerzel=\"$short\" ");
		if(count($data)>0){
			$tchrData = array("email" => $data[0][0], "id" => $data[0][1]);
			return $tchrData;
			} else {
			return null;	
			}
		
		}
		
		
		/**
		* @param $studentId;
		* @return array(String)
		*/
		public function getCoursesOfStudent($studentId){
			//MUST be separated from student's class
		$courses = array();
		$data = self::$connection(""); //Query missing - new table to be created [studentID,courseName]
		if(isset($data)) {
				foreach($data[0] as $d){
					$courses[] = $d[0];
				}
			}
		return $courses;
		}
		
		
		/**********************************************************
		******functions for CoverLesson Module in data transmission
		***********************************************************/
		
		/**
		/*Bereite DB fuer neue Eintraege vor
		*Setze alle Eintraege des Datums der geparsten Datei auf inaktiv
		*Setze das tag Feld auf 0, damit die nur die aktuell geparsten Dateien (Tage) eingetragen werden 
		*@param $dat
		*/
		public function prepareForEntry($dat){
		$dArr = explode(';',$dat);
		$datum = $dArr[0];
		$file = $dArr[1];
		$today = date('Ymd');
		self::$connection->straightQuery("UPDATE vp_vpdata SET aktiv=false WHERE datum=$datum");
		//Nur bei der ersten geparsten datei wird das tag feld auf Null gesetzt
		if ($file == 1) {self::$connection->straightQuery("UPDATE vp_vpdata SET tag=0 WHERE datum<$datum");}
		}
		
		/**
		*fuege abwesende lehrer in DB ein
		*@param absT String im Format YYYYMMDD;Lehrername
		*/
		public function insertAbsentee($absT){
		$arr = explode(";",$absT);
		$datum = $arr[0];
		$rest = $arr[1];
		$arr = explode(",",$rest);
		//DELETE all entries for this date in order to be renewed
		self::$connection->straightQuery("DELETE FROM vp_abwesendeLehrer WHERE datum=\"$datum\" ");
		foreach($arr as $r){
				self::$connection->insertValues("INSERT INTO vp_abwesendeLehrer (`alNr`,`datum`,`name`) 
				VALUES ('','$datum','$r')");
				//Response Meldung an C# Programm
				//echo "INSERT INTO abwesendeLehrer (`alNr`,`datum`,`name`) VALUES ('','$datum','$r')";
			}
		}


		/**
		*fuege blockierte Raeume in DB ein
		*@param bR String im Format YYYYMMDD;Raumnummer
		*/
		public function insertBlockedRoom($bR){
		$arr = explode(";",$bR);
		$datum = $arr[0];
		$rest = $arr[1];
		//DELETE all entries for this date in order to be renewed
		self::$connection->straightQuery("DELETE FROM vp_blockierteraeume WHERE datum=\"$datum\" ");
		$arr = explode(",",$rest);
		foreach($arr as $r){
			self::$connection->insertValues("INSERT INTO vp_blockierteraeume (`brNr`,`datum`,`name`) 
			VALUES ('','$datum','$r')");
			//Response Meldung an C# Programm
			//echo "INSERT INTO blockierteraeume (`brNr`,`datum`,`name`) VALUES ('','$datum','$r')";
			}

		}
		
		/**
		*fuege Vertretungsstunde ein
		* @param String
		*/
		public function insertCoverLesson($content){
		$POSTCoverL = new CoverLesson();
		$POSTCoverL->constructFromPOST($content);
		
		//Prüfe ob dieser Eintrag bereits vorhanden ist
		$data=self::$connection->selectAssociativeValues("SELECT * FROM vp_vpdata WHERE id=\"$POSTCoverL->id\" ");
		if (count($data)>0){
			$DBCoverL = new CoverLesson();
			$DBCoverL->ConstructFromDB($data[0]);
			$pk=$DBCoverL->primaryKey;
			self::$connection->straightQuery("UPDATE vp_vpdata SET aktiv=true,tag=$POSTCoverL->tag,stand=\"$POSTCoverL->stand\" WHERE vNr=$pk");
			//prüfe ob nur Kommentar geaendert ist
			if (strcmp($POSTCoverL->kommentar,$DBCoverL->kommentar) !== 0 ){
				$k = $POSTCoverL->kommentar;
				//Komentar updaten
				self::$connection->straightQuery("UPDATE vp_vpdata SET kommentar=\"$k\",aktiv=true,changed=CURRENT_TIMESTAMP WHERE vNr=$pk");
				}
			if($POSTCoverL->changedEntry == 1){  
				//update all fields except emailed - this is a change to the former version where emailed was set to 0
				//$POSTCoverL->emailed = 0;
				$POSTCoverL->aktiv=true;
				self::$connection->straightQuery("UPDATE vp_vpdata SET tag=$POSTCoverL->tag,datum=\"$POSTCoverL->datum\",vlehrer=\"$POSTCoverL->vTeacher\",
				klassen=\"$POSTCoverL->klassen\",stunde=\"$POSTCoverL->stunde\",fach=\"$POSTCoverL->vFach\",raum=\"$POSTCoverL->vRaum\",
				eLehrer=\"$POSTCoverL->eTeacherKurz\",eFach=\"$POSTCoverL->eFach\",kommentar=\"$POSTCoverL->kommentar\",id=\"$POSTCoverL->id\",aktiv=$POSTCoverL->aktiv,
				stand=\"$POSTCoverL->stand\",changed=CURRENT_TIMESTAMP WHERE vNr=$pk");	
				}	
			}
		else{
			//Eintrag in Datenbank
			$POSTCoverL->aktiv=true;
			self::$connection->insertValues("INSERT into vp_vpdata (`vNr`,`tag`,`datum`,`vLehrer`,`klassen`,`stunde`,`fach`,`raum`,`eLehrer`,`eFach`,`kommentar`,`id`,`aktiv`,`stand`,`changed` )
			VALUES ('','$POSTCoverL->tag','$POSTCoverL->datum','$POSTCoverL->vTeacher','$POSTCoverL->klassen','$POSTCoverL->stunde','$POSTCoverL->vFach','$POSTCoverL->vRaum',
			'$POSTCoverL->eTeacherKurz','$POSTCoverL->eFach','$POSTCoverL->kommentar','$POSTCoverL->id','$POSTCoverL->aktiv','$POSTCoverL->stand',CURRENT_TIMESTAMP)");
			}
			
		}
		
		
		
		/**
		*Debugging LogFile Entry for CoverLessonModule
		* @param String
		*/
		public function writeToVpLog($text){
			$f=fopen("vpaction.log","a");
			fwrite($f,$text."\r\n");
			fclose($f);	
			}
		
		
		
		/**
		* lese Emailbedarf aus 
		* @return mailListLehrer Array(Teacher)
		*/
		public function getMailList(){
		$mailListLehrer=array();
		//Lese Emailbedarf für Aktualisierung (neue Vertretungen )
		$data=self::$connection->selectValues("SELECT DISTINCT lehrer.id,email FROM vp_vpdata,lehrer 
		WHERE changed > emailed
		AND vp_vpdata.vLehrer=lehrer.untisName
		AND lehrer.receive_vpmail=true
		AND aktiv=true AND vlehrer NOT LIKE '%--%' 
		AND vlehrer NOT LIKE '%selbst%' 
		AND tag>0");
		
		if(count($data)>0){
			foreach($data as $d) {
			//Diese Lehrer müssen eine Email erhalten
			$mailListLehrer[] = $this->addToEmailList($d[0],$d[1]);
			}
		}
		//bei diesen Lehrern entfällt etwas
		$data=self::$connection->selectValues("SELECT DISTINCT lehrer.id,email 
		FROM vp_vpdata,lehrer 
		WHERE vp_vpdata.eLehrer=lehrer.kuerzel
		AND changed > emailed
		AND lehrer.receive_vpmail=true
		AND aktiv=true 
		AND (vlehrer LIKE \"%--%\" OR vlehrer LIKE \"%selbst%\") AND tag>0 ");
		if (count($data)>0) {
		foreach($data as $d){
		//Prüfe ob dieser Lehrer schon in der EmailListe ist
		if($this->mustAddToList($mailListLehrer,$d[0])) {
				$mailListLehrer[] = $this->addToEmailList($d[0],$d[1]);
				}
			}
		}
		//bei diesen Lehrern wurde eine Vertretung gestrichen
		$data=self::$connection->selectValues("SELECT DISTINCT lehrer.id,email FROM vp_vpdata,lehrer 
		WHERE aktiv=false
		AND vp_vpdata.vLehrer=lehrer.untisName
		and lehrer.receive_vpmail=true
		AND vlehrer NOT LIKE '%--%' 
		AND vlehrer NOT LIKE '%selbst%' 
		AND tag>0");
		if (count($data)>0) {
		foreach($data as $d){
		//Prüfe ob dieser Lehrer schon in der EmailListe ist
		if($this->mustAddToList($mailListLehrer,$d[0])) {
				$mailListLehrer[]=$this->addToEmailList($d[0],$d[1]);
				}
		}
	}
	return $mailListLehrer;
	}
	
	/**
	* adds a Teacher Object to the Emaillist
	* @param int teacherId
	* @return Teacher Object
	*/
	private function addToEmailList($id,$email){
		$teacher = new Teacher($email,$id); //adapt to Teacher class constructor
		$teacher->getData();
		$teacher->setVpInfoDate($this->getUpdateTime() );
		return $teacher;
		}
	/**
	*
	* check if teacher must be added to EmailList
	* @param array()
	* @param int 
	* @return bool
	*/
	private function mustAddToList($list,$id){
		if(count($list) == 0) {return true;}
		foreach($list as $l){
			if($l->getId() == $id) {
				//already included
				return false;
				break;
				}
			}
	return true;
	}
	
		
	/**
	*Trage Datum des Email Versands in die Datenbank ein
	*@param entry Id des CoverLesson Datensatzes
	*/
	public function updateVpMailSentDate($entry){
		self::$connection->straightQuery("UPDATE vp_vpdata set emailed = CURRENT_TIMESTAMP WHERE vnr=$entry");
		}
	/**
	* delete all inactive entries in coverLessontable
	*/
	public function deleteInactiveEntries(){
		self::$connection->straightQuery("DELETE FROM vp_vpdata WHERE aktiv=false");
		}

	
	/**
	* create mail content for automated cover lesson email
	* @param Teacher Object
	* Return String
	*/
	public function makeHTMLVpMailContent($teacher){
	$coverLessonNrs = array();	
	$data = $this->getCoverLessonsByTeacher($teacher);	
	$linkStyle='style="font-family:Arial,Sans-Serif;font-size:12px;font-weight:bold;color: #86160e;font-decoration:underline;"';
	$vnArr=array();
	$content=mb_convert_encoding('<table><tr><td style="color:#000000;font-family:Arial,Sans-Serif;font-weight:bold;font-size:14px;">Übersicht für '.
	$teacher->getSurname().', '.$teacher->getName().'</td><td style="color:#000000;font-family:Arial,Sans-Serif;font-weight:bold;font-size:9px;"> 
	(Stand: '.$teacher->getVpInfoDate().')</td></tr></table><br/>','UTF-8');	
	if(!isset($data)) {
		$content .= "<p><b>Keine Vertretungen!</b></p>";
		}
	else {
		//make headers
		$content .= '<table>';
		$v = $data[0];
		$content .= '<tr style="font-family:Arial,Sans-Serif;font-size:12px;font-weight:bold;color:#ffffff; background-color: #86160e;">';
		$colcounter = 0;
		foreach ($v as $key => $value) {
			if ($colcounter > 0) {
				$content .= '<td><b>'.$key.'</b></td>';
				}
				$colcounter++;
			}
		$content .= '</tr>';
		//lines containing cover lessons
		$zeile = true;
		foreach ($data as $v){
			$colcounter = 0;
			if($zeile) {$style='style="font-family:Arial,Sans-Serif;font-size:12px; background-color:#cccccc;';$zeile=false;}
			else {$style='style="font-family:Arial,Sans-Serif;font-size:12px; background-color:#eeeeee;';$zeile=true;}
			if($v["Vertreter"] == $teacher->getUntisName()) {$style=$style.'color:#ff0000;"';} else {$style=$style.'color:#000000;"';}	
			$content .= '<tr '. $style .'>';
			foreach ($v as $key => $value) {
			if ($colcounter > 0){
				$content .= '<td>'.$value.'</td>';
				}
			else {
				$coverLessonNrs[] = $v["vnr"];
				}
			$colcounter ++;
			}
			$content .= '</tr>';
			}
		}
	
	$content .= '</table>';
	$subscriptionInfo='<p style="font-family:Arial,Sans-Serif;font-size:12px; font-weight:bold;">'.mb_convert_encoding('<br><br>Diese Email wurde automatisch versendet. Die
	Einstellung zum Emailversand können Sie jederzeit in der <a '.$linkStyle.' href="http://www.suso.schulen.konstanz.de/intern">Suso-Intern-Anwendung</a> (Login erforderlich) ändern.<br>
	Bitte melden Sie Unregelmäßigkeiten oder Fehler im Emailversand.<br><br>Vielen Dank für Ihre Unterstützung!','UTF-8').'</p>';
	
	$teacher->setCurrentCoverLessonNrs($coverLessonNrs);
	return $mailContent = $content.$subscriptionInfo.'<br>';
	
	}
				
}


?>
