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
        
        return new Student($data['id'], $data['klasse'], $data['name'], $data['vorname'], $data['gebdatum'], $data['eid']);
    }
    
    /**
     * @param $uid int
     *
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
     *
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
        
        return array("name" => $name, "surname" => $surname);
    }
    
    
    /**
     * @param int $usrId UserId
     *
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
     *
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
     *
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
    
    
    public function getStudentUserById($id) {
        $data = self::$connection->selectAssociativeValues("SELECT * FROM schueler WHERE id='$id'");
        
        if (!isset($data[0])) {
            return null;
        }
        $data = $data[0];
        
        return new StudentUser($data['id'], $data['name'], $data['vorname'], $data['klasse'], $data['gebdatum'], $data['eid'], $data['kurse']);
        
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
                    
                    return new StudentUser($data['id'], $data['name'], $data['vorname'], $data['klasse'], $data['gebdatum'], $data['eid'], $data['kurse']);
                    
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
     *
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
        $data = self::$connection->selectValues("SELECT time_slot.id,bookable_slot.id,bookable_slot.lid FROM time_slot,bookable_slot
			WHERE time_slot.id=bookable_slot.slotid
			AND bookable_slot.eid='$parentId' ORDER BY anfang");
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
     * @param $email
     * @param $password
     *
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
     * @param $pid     array or int parents children ids (array[int] || int)
     * @param $email   string parents email
     * @param $pwd     string parents password
     * @param $name    string parent name
     * @param $surname string parent surname
     *
     * @return array newly created ids of parent (userid and parentid)
     */
    public function registerParent($email, $pwd, $name, $surname) {
        
        $email = self::$connection->escape_string($email);
        $pwd = self::$connection->escape_string($pwd);
        $name = self::$connection->escape_string($name);
        $surname = self::$connection->escape_string($surname);
        
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
            
            $query = "UPDATE schueler SET eid=$parentId WHERE id='$id';";
            self::$connection->straightQuery($query);
        }
        
        
        return true;
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
     *
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
     *
     * @param $staff boolean
     *
     * @return Array(Terminobjekte)
     */
    public function getNextDates($staff) {
        $staff ? $query = "SELECT typ,start,ende,staff FROM termine ORDER BY start" : $query = "SELECT typ,start,ende,staff FROM termine WHERE staff=0 ORDER BY start";
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
    public function updateUserData($usrId, $name, $surname, $email, $getnews, $htmlnews) {
        
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
        
        foreach ($data as $day) {
            $allDays[] = array("timestamp" => $day[0], "dateAsString" => $this->getDateString($day[0]));
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
        $weekdays = array('So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa');
        $month = $date[4] . $date[5];
        $day = $date[6] . $date[7];
        $year = $date[0] . $date[1] . $date[2] . $date[3];
        $date = getdate(mktime(0, 0, 0, $month, $day, $year));
        $dayOfWeek = $date['wday'];
        
        return $weekdays[$dayOfWeek];
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
            
            if (count($data) > 0) {
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
            if (count($data) > 0) {
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
        if (count($data) > 0) {
            $tchrData = array("email" => $data[0][0], "id" => $data[0][1]);
            
            return $tchrData;
        } else {
            return null;
        }
        
    }
    
    /**
     *get Primary key and email by Teacher untisname
     *
     * @param String $short
     *
     * @return array(String, Int)
     */
    public function getTeacherDataByShortName($short) {
        $tchrData = array();
        $short = self::$connection->escape_string($short);
        $data = self::$connection->selectValues("SELECT email,id FROM lehrer WHERE kuerzel='$short' ");
        if (count($data) > 0) {
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
        $data = self::$connection->selectValues("SELECT userid,eltern.id,htmlnews,email FROM eltern,user,schueler
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
    
    
}


?>
