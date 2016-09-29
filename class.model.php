<?php

/**
 *
 */
class Model
{
    private $connection;//Connection Object

    /**
     *Konstruktor
     */
    function __construct()
    {
        $this->connection = new Connection();
    }

    /**
     * Schuelerexistenz prüfen
     * @param string $vorname Schueler Vorname
     * @param string $name Schueler Nachname
     * @param string $bday Geburtsdatum
     * @return int SchuelerID
     **/
    public function checkPupilExist($vorname, $name, $bday)
    { //TODO -> $name und $vorname beinhalten auch zweit namen -> optional oder pflicht bei registierung?
        $id = null;
        $name = $this->connection->escape_string($name);
        $bday = $this->connection->escape_string($bday);
        $data = $this->connection->selectValues("SELECT id FROM schueler WHERE vorname='$vorname' AND name='$name' AND gebdatum='$bday' AND eid IS NULL");
        if (isset($data[0][0]))
            $id = $data[0][0];
        return $id;
    }

    /**
     * @param int $userid
     */
    public function userGetType($userid)
    {
        $data = $this->connection->selectValues("SELECT user_type FROM user WHERE id=$userid");

    }

    /**
     * @param string $userName
     */
    public function usernameGetId($userName)
    {
        $userName = $this->connection->escape_string($userName);
        $data = $this->connection->selectValues("SELECT id FROM user WHERE username='$userName'");

    }

    /**
     * @param int $userId
     */
    public function parentGetName($userId)
    {
        $data = $this->connection->selectValues("SELECT eltern.* FROM eltern, user WHERE eltern.userid=user.id AND user.id=$userId AND user.user_type=1");

    }

    /**
     * @param int $userId
     */
    public function teacherGetName($userId)
    {
        $data = $this->connection->selectValues("SELECT lehrer.* FROM lehrer, user WHERE lehrer.userid=user.id AND user.id=$userId AND user.user_type=2");

    }

    /**
     * @param int $elternId
     */
    public function parentGetChildren($elternId)
    {
        $data = $this->connection->selectValues("SELECT * FROM schueler WHERE eid=$elternId");
    }

    /**
     * @param int $schuelerId
     */
    public function studentGetClass($schuelerId)
    {
        $data = $this->connection->selectValues("SELECT schueler.klasse FROM schueler WHERE id=$schuelerId");
    }

    /**
     * @param string $class
     */
    public function classGetTeachers($class)
    {
        $class = $this->connection->escape_string($class);
        $data = $this->connection->selectValues("SELECT lehrer.* FROM lehrer, unterricht WHERE unterricht.klasse='$class' AND unterricht.lid=lehrer.id");

    }

    /**
     * @param int $terminId
     */
    public function bookingAdd($terminId)
    {

    }

    /**
     * @param int $terminId
     */
    public function bookingDeleate($terminId)
    {

    }

    /**
     * @param $userName
     * @param $password
     */
    public function passwordValidate($userName, $password)
    { //TODO gehört das überhaupt in das model?

    }
}


?>
