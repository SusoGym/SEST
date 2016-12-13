<?php

    /**
     * The model class
     */
    class Model
    {
        /**
         * @var Connection
         */
        protected static $connection;
        /**
         * @var Model
         */
        protected static $model;

        /**
         *Konstruktor
         */
        protected function __construct()
        {
            if (self::$connection == null)
                self::$connection = new Connection();

        }

        static function getInstance()
        {
            return self::$model == null ? self::$model = new Model() : self::$model;
        }

        /**
         *getOptions
         *returns option from DB table options
         *e.g. slot assignment, booking period, allowed bookings etc
         *
         * @return array()
         */
        public function getOptions()
        {
            $options = array();
            $data = self::$connection->SelectAssociativeValues("SELECT * FROM options");

            foreach ($data as $d)
            {
                $options[$d['type']] = $d['value'];

            }

            return $options;
        }


        /**
         * @param string $vorname Schueler Vorname
         * @param string $name Schueler Nachname
         * @return Student
         **/
        public function getStudentByName($name, $surname = null)
        {

            $name = self::$connection->escape_string($name);
            if ($surname != null)
            {
                $surname = self::$connection->escape_string($surname);
                $wholeName = str_replace(' ', '', $name . $surname);
            } else
            {
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
        public function getUserById($uid, $data = null)
        {

            if ($data == null)
                $data = self::$connection->selectAssociativeValues("SELECT * FROM user WHERE id=$uid");
            if ($data == null)
                return null;
            if (isset($data[0]))
                $data = $data[0];

            $type = $data['user_type'];

            switch ($type)
            {
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
        public function getUserByMail($email)
        {
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
        public function getTeacherNameByTeacherId($teacherId, $data = null)
        {
            if ($data == null)
                $data = self::$connection->selectAssociativeValues("SELECT * FROM lehrer WHERE lehrer.id=$teacherId");

            if (isset($data[0]))
                $data = $data[0];

            $surname = isset($data["name"]) ? $data["name"] : null;
            $name = isset($data["vorname"]) ? $data["vorname"] : null;

            return array("name" => $name, "surname" => $surname);
        }

        /**
         * getTeacherName and Id when logged in via LDAP Login
         *
         * @param LDAPName string
         * @return
         */
        public function getTeacherDetailsByLDAPName($ldapName)
        {

            $data = self::$connection->selectAssociativeValues("SELECT * FROM lehrer WHERE lehrer.ldapname=$ldapName;");

            $surname = $data[0]["name"];
            $name = $data[0]["vorname"];
            $id = $data[0]["id"];
            $email = $data[0]["email"];
            $deputat = $data[0]["deputat"];

            die(json_encode($data));

            return array("name" => $name, "surname" => $surname, "ldap" => $ldapName, "teacherId" => $id, "email" => $email, "deputat" => $deputat);
        }

        /**
         * @param int $usrId UserId
         * @return array[Student] array[childrenId]
         */
        public function getChildrenByParentUserId($usrId)
        {
            $data = self::$connection->selectAssociativeValues("SELECT schueler.* FROM schueler, eltern WHERE schueler.eid=eltern.id AND eltern.userid=$usrId");

            if ($data == null)
                return array();

            $students = array();

            foreach ($data as $item)
            {
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
        public function getStudentById($studentId)
        {
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
        public function getTeachersByClass($class)
        {
            $class = self::$connection->escape_string($class);
            $data = self::$connection->selectValues("SELECT lehrer.id FROM lehrer, unterricht WHERE unterricht.klasse='$class' AND unterricht.lid=lehrer.id"); // returns data[n][data]

            if ($data == null)
                return null;

            $ids = array();
            foreach ($data as $item)
            {
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
        public function getTeachers()
        {
            $data = self::$connection->selectAssociativeValues("SELECT * FROM lehrer"); // returns data[n][data]

            $teachers = array();
            foreach ($data as $item)
            {
                $tid = intval($item['id']);
                array_push($teachers, $this->getTeacherByTeacherId($tid, $item));
            }

            return $teachers;
        }

        /**
         * @param $tchrId int teacherId
         * @return Teacher
         */
        public function getTeacherByTeacherId($tchrId, $data = null)
        {
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
        public function getTeacherLdapNameByTeacherId($teacherId, $rawData = null)
        {
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
        public function getTeacherLessonAmountByTeacherId($teacherId, $rawData = null)
        {
            return 18; //TODO
        }

        /**
         * @param $email
         * @param $pwd
         * @return Teacher | null
         */
        public function getTeacherByEmailAndLdapPwd($email, $pwd)
        {

            $data = self::$connection->selectAssociativeValues("SELECT * FROM lehrer WHERE email='$email'");

            if (isset($data[0]))
                $data = $data[0];

            if ($data == null)
                return null;

            $tId = $data['id'];
            $ldapName = $this->getTeacherLdapNameByTeacherId($tId, $data);

            if ($ldapName == null)
                die("LDAP name not set for $email!"); // rip

            $novelData = $this->checkNovellLogin($ldapName, $pwd);

            if (!isset($novelData->{'code'}) || !isset($novelData->{'type'}) || $novelData->{'code'} != "200" || $novelData->{'type'} != 'Teacher')
                return null; //Invalid / Failed login

            return new Teacher($email, $tId);
        }

        /**
         *returns if slot already assigned - reloading
         *
         * @param int slotId
         * @param int teacherId
         * @return bool
         */
        private function checkAssignedSlot($slotId, $teacherId)
        {
            $data = self::$connection->selectvalues("SELECT slotid FROM bookable_slot WHERE slotid=$slotId AND lid=$teacherId");
            if (isset($data))
            {
                return true;
            } else
            {
                return false;
            }

        }

        /**
         *get existing slots for parent-teacher meeting
         *
         * @return array(array("id","start","ende"))
         */
        public function getSlots()
        {
            $slots = array();
            $data = $tchrs = self::$connection->selectValues("SELECT id,anfang,ende FROM time_slot ORDER BY anfang ");
            if (isset($data))
            {
                foreach ($data as $d)
                {
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
        public function setAssignedSlot($slot, $teacherId)
        {
            if (!$this->checkAssignedSlot($slot, $teacherId))
            {
                self::$connection->straightQuery("INSERT INTO bookable_slot (`slotid`,`lid`) VALUES ('$slot','$teacherId')");
            }
        }

        /**
         *deletes an assigned Slot from DB
         *
         * @param slotId
         * @param teacherId
         */
        public function deleteAssignedSlot($slotId, $teacherId)
        {
            self::$connection->straightQuery("DELETE FROM bookable_slot WHERE slotid=$slotId AND lid=$teacherId");
        }


        /**
         *returns assigned slots of a teacher
         *
         * @param int teacherId
         * @returns array(int)
         */
        public function getAssignedSlots($teacher)
        {
            $slots = array();
            $data = self::$connection->selectValues("SELECT slotid FROM bookable_slot WHERE lid=$teacher");
            if (isset($data))
            {
                foreach ($data as $d)
                {
                    $slots[] = $d[0];
                }
            }

            return $slots;
        }



        /**
         * @param $eid int parentId
         * @return Guardian
         */
        public function getParentByParentId($eid)
        {
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
        public function bookingAdd($slotId, $userId)
        {
            return self::$connection->insertValues("UPDATE bookable_slot SET eid=$userId WHERE id=$slotId");
        }

        /**
         * @param int $appointment
         */
        public function bookingDelete($appointment)
        {
            self::$connection->straightQuery("UPDATE bookable_slot SET eid=NULL WHERE id=$appointment");
        }

        /**
         * @param $parentId int
         * @param $appointment int
         * @return boolean
         */
        public function parentOwnsAppointment($parentId, $appointment)
        {
            $data = self::$connection->selectAssociativeValues("SELECT * FROM bookable_slot WHERE id=$appointment");
            if(isset($data[0]))
                $data = $data[0];
            if(!isset($data) || $data['eid'] == null)
                return true; //throw exception?
            return $data['eid'] == $parentId;
        }

        /**
         * @param $slotId int
         * @param $userId int
         * @return int appointmentId
         */
        public function getAppointment($slotId, $userId)
        {
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
        public function getAllBookableSlotsForParent($teacherId, $parentId)
        {
            $slots = array();
            $data = self::$connection->selectValues("SELECT bookable_slot.id,anfang,ende,eid,time_slot.id FROM bookable_slot,time_slot 
			WHERE lid=$teacherId
			AND bookable_slot.slotid=time_slot.id
			AND (eid IS NULL OR eid=$parentId)
			ORDER BY anfang");
            if (isset($data))
            {
                foreach ($data as $d)
                {
                    $slots[] = array("bookingId" => $d[0], "anfang" => $d[1], "ende" => $d[2], "eid" => $d[3], "slotId" => $d[4]);
                }
            }

            return $slots;
        }

        /**
         *returns appointments of parent
         *
         * @param int parentId
         * @return array(Timestamp anfang)
         */
        public function getAppointmentsOfParent($parentId)
        {
            $appointments = array();
            $data = self::$connection->selectValues("SELECT time_slot.id FROM time_slot,bookable_slot
			WHERE time_slot.id=bookable_slot.slotid
			AND bookable_slot.eid=$parentId ORDER BY anfang");
            if (isset($data))
            {
                foreach ($data as $d)
                {
                    $appointments[] = $d[0];
                }
            }

            return $appointments;
        }

        /**
         * @param $email
         * @param $password
         * @return bool user exists in database and password is equal with the one in the database
         */
        public function passwordValidate($email, $password)
        {

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
         * @return array newly created ids of parent (userid and parentid)
         */
        public function registerParent($email, $pwd)
        {

            $email = self::$connection->escape_string($email);
            $pwd = password_hash($pwd, PASSWORD_DEFAULT);

            $query = "INSERT INTO user (user_type, password_hash, email) VALUES (1,'$pwd', '$email');";

            //Create parent in database and return eid
            $usrId = self::$connection->insertValues($query);

            $parentId = self::$connection->insertValues("INSERT INTO eltern (userid) VALUES ($usrId);");

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
        public function parentAddStudents($parentId, $studentIds)
        {

            if (!is_array($studentIds))
                $studentIds = array($studentIds);

            $parent = $this->getParentByParentId($parentId);

            if ($parent == null)
                return false;

            $query = "";

            foreach ($studentIds as $id)
            {
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
        function checkNovellLogin($usr, $pwd)
        {

            $apiUrl = "https://intranet.suso.schulen.konstanz.de/gpuntis/est.php"; //TODO: do by config or sth
            $headers = array('Authorization: Basic ' . base64_encode("$usr:$pwd"));

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiUrl);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); //fixme: ssl unsafe!!! -> is certificate correctly installed @ server? if yes we can remove this file and make everything save

            $result = utf8_encode(curl_exec($ch));
            if (curl_errno($ch))
            {
                throw new Exception(curl_error($ch));
            }

            if ($result == false)
            {
                throw new Exception("Response was empty!");
            }

            $res = json_decode($result);

            ChromePhp::info("Response from ldap [$usr, $pwd]: " . json_encode($res));

            return $res;


        }

    }


?>
