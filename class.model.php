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
                    $parentId = self::$connection->selectAssociativeValues("SELECT id FROM eltern WHERE userid=$uid")[0]['id'];

                    return new Guardian($data['id'], $data['email'], $parentId);
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

            return $rawData['ldapname'];
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



    }


?>
