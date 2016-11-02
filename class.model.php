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
     * Schuelerexistenz prüfen
     * @param string $vorname Schueler Vorname
     * @param string $name Schueler Nachname
     * @param string $bday Geburtsdatum
     * @return array[int] array[SchuelerID, ElternId] TODO: would be nicer to create student object here
     **/
    public function checkPupilExist($name, $bday)
    { //TODO -> $name und $vorname beinhalten auch zweit namen -> optional oder pflicht bei registierung?

        $name = self::$connection->escape_string($name);

        $data = self::$connection->selectValues("SELECT id, eid, name, vorname FROM schueler WHERE Replace(CONCAT(vorname, name), ' ', '') = '$name' AND gebdatum='$bday'");

        $id = null;
        $eid = null;
        $name = null;
        $vorname = null;

        if (isset($data[0][0]))
            $id = $data[0][0];
        if (isset($data[0][1]))
            $eid = $data[0][1];
        if (isset($data[0][2]))
            $name = $data[0][2];
        if (isset($data[0][3]))
            $vorname = $data[0][3];

        if ($id == null)
            return null;

        return array("id" => $id, "eid" => $eid, "name" => $name, "vorname" => $vorname);
    }

    /**
     * @param int $userid
     * @return int userType [0 - admin; 1 - parent; 2 - teacher]
     */
    public function userGetType($userid)
    {
        $data = self::$connection->selectValues("SELECT user_type FROM user WHERE id=" . $userid);

        if (!isset($data[0]))
            return null;

        return intval($data[0][0]);

    }

    /**
     * @param string $email the user email
     * @return int userId
     */
    public function userGetIdByMail($email)
    {
        $email = self::$connection->escape_string($email);
        $data = self::$connection->selectValues("SELECT id FROM user WHERE email='$email'");

        if ($data == null)
            return null;

        return $data[0][0];
    }

    /**
     * @param $id int userId
     * @return string email
     */
    public function idGetEmail($id)
    {
        $data = self::$connection->selectValues("SELECT email FROM user WHERE id=$id");

        if ($data == null)
            return null;

        return $data[0][0];
    }

    /**
     * @param int $userId
     * @return string
     */
    public function parentGetName($userId)
    {
        $data = self::$connection->selectAssociativeValues("SELECT eltern.* FROM eltern, user WHERE eltern.userid=user.id AND user.id=$userId AND user.user_type=1");

        $name = $data["name"];
        $surname = $data["vorname"];

        return $surname . " " . $name; //TODO: really?

    }

    /**
     * @param int $tchrId
     * @param string $sort
     * @return array(surname, name)
     */
    public function teacherGetName($tchrId, $sort = "name ASC")
    {
        $data = self::$connection->selectAssociativeValues("SELECT * FROM lehrer WHERE id=$tchrId ORDER BY $sort");

        $name = $data[0]["name"];
        $surname = $data[0]["vorname"];

        return array('surname' => $surname, 'name' => $name);
    }

    /**
     * @param int $elternId
     * @return array[int] array[childrenId]
     */
    public function parentGetChildren($elternId)
    {
        $data = self::$connection->selectValues("SELECT * FROM schueler WHERE eid=$elternId");

        $ids = array();

        foreach ($data as $item) {
            $pid = intval($item[0]);
            array_push($ids, $pid);
        }

        return $ids;
    }

    /**
     * @param int $schuelerId
     * @return string
     */
    public function studentGetClass($schuelerId)
    {
        $data = self::$connection->selectValues("SELECT schueler.klasse FROM schueler WHERE id=$schuelerId");

        return $data[0][0];
    }

    /**
     * @param string $class
     * @return array[int] array with teacherIds
     */
    public function classGetTeachers($class)
    {
        $class = self::$connection->escape_string($class);
        $data = self::$connection->selectValues("SELECT lehrer.id FROM lehrer, unterricht WHERE unterricht.klasse='$class' AND unterricht.lid=lehrer.id"); // returns data[n][data]

        $ids = array();

        foreach ($data as $item) {
            $tid = intval($item[0]);
            array_push($ids, $tid);
        }

        return $ids;

    }

    public function getTeachers()
    {
        $data = self::$connection->selectValues("SELECT id FROM lehrer"); // returns data[n][data]

        $ids = array();

        foreach ($data as $item) {
            $tid = intval($item[0]);
            array_push($ids, $tid);
        }

        return $ids;
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
