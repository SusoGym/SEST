<?php namespace administrator;

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
     *read datafields from database
     * @param bool $student
     * @return array datafield names
     */
    public function readDBFields($student)
    {
        ($student) ? $table = "schueler" : $table = "lehrer";
        $data = self::$connection->selectFieldNames("SELECT * FROM " . $table);
        return $data;

    }


    /**
     * @param $mail
     * @param $password
     * @return bool user exists in database and password is equal with the one in the database
     */
    public function passwordValidate($mail, $password)
    {

        $mail = self::$connection->escape_string($mail);
        //$password = self::$connection->escape_string($userName);

        $data = self::$connection->selectAssociativeValues("SELECT password_hash from user WHERE email='$mail'");

        if ($data == null)
            return false;


        $data = $data[0];

        $pwd_hash = $data['password_hash'];


        return password_verify($password, $pwd_hash);
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
     *check if data exist in database
     * @param int $id
     * @param bool $student
     * @return bool existence
     */
    public function checkDBData($student, $id)
    {
        ($student) ? $table = "schueler" : $table = "lehrer";
        $data = self::$connection->selectValues("SELECT id FROM $table where id=$id");
        if (count($data) > 0) {
            return true;
        } else {
            return false;
        }
    }


    /**
     *update data
     * @param bool
     * @param int
     * @param array
     */
    public function updateData($student, $id, $line)
    {
        ($student) ? $table = "schueler" : $table = "lehrer";
        $string = null;
        foreach ($line as $key => $value) {
            $key = trim($key);
            $value = trim($value);

            if (isset($string)) {
                $string = $string . ",$key=\"$value\" ";
            } else {
                $string = "$key=\"$value\" ";
            }
        }
        $string = $string . ",upd=1 WHERE id=$id";
        $string = "UPDATE $table SET " . $string;
        self::$connection->straightQuery($string);
    }

    /**
     *insert data
     * @param bool
     * @param array
     */
    public function insertData($student, $line)
    {
        ($student) ? $table = "schueler" : $table = "lehrer";
        $fieldstring = null;
        $valuestring = null;
        foreach ($line as $key => $value) {
            $key = trim($key);
            $value = trim($value);
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
        self::$connection->insertValues($string);

    }

    /**
     * delete unused data from DB
     * @param bool $student
     * @return int amount of deletions
     */
    public function deleteDataFromDB($student)
    {
        $toDelete = 0;
        ($student) ? $table = "schueler" : $table = "lehrer";
        $data = self::$connection->selectValues("SELECT id FROM $table WHERE upd=0");
        $toDelete = count($data);
        self::$connection->straightQuery("DELETE FROM $table WHERE upd=0");
        return $toDelete;
    }

    /**
     *set update status to zero
     * @param bool $student
     */
    public function setUpdateStatusZero($student)
    {
        ($student) ? $table = "schueler" : $table = "lehrer";
        self::$connection->straightQuery("UPDATE $table SET upd=0");
    }
	
	
	/**
     * get all teachers
     * @return array[int] array with teacherIds
     */
    public function getTeachers()
    {
        $data = self::$connection->selectValues("SELECT id FROM lehrer order by Name"); // returns data[n][data]

        $ids = array();

        foreach ($data as $item)
        {
            $tid = intval($item[0]);
            array_push($ids, $tid);
        }
		return $ids;

    }
	/**
     * @param int $tchrId, string $sort
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
	*
	*@return array(string) with form names
	*/
	public function getForms()
    {
        $data = self::$connection->selectValues("SELECT DISTINCT klasse FROM schueler order by klasse"); // returns data[n][data]

        $forms = array();

        foreach ($data as $item)
        {
            array_push($forms, $item[0]);
        }
		
		return $forms;

    }
	
	
	/**
	* connect teacher with form
	* @param array(int)  with teacer Ids
	* @param string Klasse
	*/
	public function setTeacherToForm($teacher,$form){
		//check if this connection exists
		self::$connection->straightQuery("DELETE FROM unterricht WHERE klasse=\"$form\" ");
		if(isset($teacher)) {
		foreach($teacher as $t){
			self::$connection->insertValues("INSERT INTO unterricht (`id`,`lid`,`klasse`) VALUES ('','$t','$form')");
			}	
		}
		
		
	}
	
	/**
	* get teachers in form
	* @param string form
	* @return array[teacherId](array(form) )
	*/
	public function getTeachersOfForm($form){
		$teachersOfForm = array();
		$teachers = array();
		$tchrs = self::$connection->selectValues("SELECT lid FROM unterricht WHERE klasse=\"$form\" ");
				if(count($tchrs) > 0){
					foreach($tchrs as $t){
						$teachers[] = $t[0];
						}
					}
				else{
					$teachers=null;
					}
		
		$teachersOfForm[$form] = $teachers;
		unset($teachers);			
		return $teachersOfForm;	
	}

}


?>
