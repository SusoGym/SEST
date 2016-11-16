<?php

/**
 * The model class
 */
class Model
{
    /**
     * @var Connection
     */
    private static $connection;
    /**
     * @var Model
     */
    private static $model;

    /**
     *Konstruktor
     */
    private function __construct()
    {
        if (self::$connection == null)
            self::$connection = new Connection();

    }

    static function getInstance()
    {
        return self::$model == null ? self::$model = new Model() : self::$model;
    }


    /**
     * @param string $vorname Schueler Vorname
     * @param string $name Schueler Nachname
     * @return Student
     **/
    public function getStudentByName($name, $surname = null)
    { //TODO -> $name und $vorname beinhalten auch zweit namen -> optional oder pflicht bei registierung bzw. muss man noch aus db löschen...?

        $name = self::$connection->escape_string($name);
        if ($surname != null) {
            $surname = self::$connection->escape_string($surname);
            $wholeName = str_replace(' ', '', $name . $surname);
        } else {
            $wholeName = $name;
        }

        $data = self::$connection->selectValues("SELECT * FROM schueler WHERE Replace(CONCAT(vorname, name), ' ', '') = '$wholeName'");

        if ($data == null)
            return null;

        $data = $data[0];

        return new Student($data['id'], $data['klasse'], $data['name'], $data['vorname'], $data['eid']);
    }

    /**
     * @param $uid int
     * @return User
     */
    public function getUserById($uid)
    {
        $data = self::$connection->selectAssociativeValues("SELECT * FROM user WHERE id=$uid");
        if ($data == null)
            return null;
        $data = $data[0];

        $type = $data['user_type'];

        switch ($type) {
            case 0: // Admin
                return new Admin($data['id'], $data['username'], $data['email']);
                break;
            case 1: // Parent / Guardian
                $parentId = self::$connection->selectAssociativeValues("SELECT id FROM eltern WHERE userid=$uid")[0]['id'];
                return new Guardian($data['id'], $data['username'], $data['email'], $parentId);
            case 2:
                $id = self::$connection->selectAssociativeValues("SELECT id FROM lehrer WHERE userid=$uid")[0]['id'];
                return new Teacher($data['id'], $data['username'], $data['email'], $id);
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
        $data = self::$connection->selectAssociativeValues("SELECT id FROM user WHERE email='$email'");

        if ($data == null)
            return null;

        return $this->getUserById($data[0]['id']);
    }

    /**
     * @param int $userId
     * @return string
     * FIXME: Never used, empty in db...
     */
    public function parentGetName($userId)
    {
        $data = self::$connection->selectAssociativeValues("SELECT eltern.* FROM eltern, user WHERE eltern.userid=user.id AND user.id=$userId AND user.user_type=1");

        if(!isset($data[0]))
            return array("name" => null, "surname" => null);

        $data = $data[0];

        $surname = $data["name"];
        $name = $data["vorname"];

        return array("name" => $name, "surname" => $surname);

    }

    /**
     * @param int $tchrId
     * @return array[String => String]
     */
    public function getTeacherNameByTeacherId($teacherId)
    {
        $data = self::$connection->selectAssociativeValues("SELECT * FROM lehrer WHERE lehrer.id=$teacherId");

        if(!isset($data[0]))
            return array("name" => null, "surname" => null);

        $data = $data[0];

        $surname = $data["name"];
        $name = $data["vorname"];

        return array("name" => $name, "surname" => $surname);
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
        foreach ($data as $item) {
            $tid = intval($item[0]);
            array_push($ids, $this->getTeacherByTeacherId($tid));
        }

        return $ids;

    }

    /**
     * Returns all Teachers
     * @return array[Teacher]
     */
    public function getTeachers()
    {
        $data = self::$connection->selectValues("SELECT id FROM lehrer"); // returns data[n][data]

        $teachers = array();

        foreach ($data as $item) {
            $tid = intval($item[0]);
            array_push($teachers, $this->getTeacherByTeacherId($tid));
        }

        return $teachers;
    }

    /**
     * @param $tchrId int teacherId
     * @return Teacher
     */
    public function getTeacherByTeacherId($tchrId)
    {
        $data = self::$connection->selectAssociativeValues("SELECT * FROM lehrer WHERE id=$tchrId");
        if ($data == null)
            return null;
        $data = $data[0];

        $usrId = $data['userid'];
        if ($usrId == -1) { // teacher has not registered yet...
            return new Teacher(null, null, $data['email'], $data['id']);
        }

        return $this->getUserById($usrId);
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
    public function bookingAdd($slotId, $userId, $teacherId)
    {
        return -1;
    }

    /**
     * @param int $appointment
     */
    public function bookingDelete($appointment)
    {

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
     * @return int newly created parents id
     */
    public function registerParent($pid, $email, $pwd)
    {

        $email = self::$connection->escape_string($email);
        $pwd = password_hash($pwd, PASSWORD_DEFAULT);

        $query = "INSERT INTO user (user_type, password_hash, email) VALUES (1,'$pwd', '$email');";

        //Create parent in database and return eid
        $usrId = self::$connection->insertValues($query);

        $parentId = self::$connection->insertValues("INSERT INTO eltern (userid) VALUES ($usrId);");

        // transform given int into array
        if (!is_array($pid)) {
            $pid = array($pid);
        }

        ChromePhp::info("New parents id is $parentId and student ids are " . json_encode($pid));

        // query each given pupil and set eid (one query to spare resources)
        $query = "";
        foreach ($pid as $pupilId) {
            $query .= "UPDATE schueler SET eid=$parentId WHERE id=$pupilId;";
        }

        self::$connection->straightQuery($query);

        //return eid
        return intval($parentId);

    }

}


?>
