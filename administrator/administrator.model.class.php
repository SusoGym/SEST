<?php namespace administrator;

/**
 * The model class
 */
class Model extends \Model
{

    /**
     * @var Model
     */
    protected static $model;

    /**
     *Konstruktor
     */
    protected function __construct()
    {
        parent::__construct();

    }

    static function getInstance()
    {
        return (self::$model == null || !(self::$model instanceof Model)) ? self::$model = new Model() : self::$model;
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
	*
	*@return array(string) with form names
	*/
	public function getForms()
    {
        $data = self::$connection->selectValues("SELECT DISTINCT klasse FROM schueler ORDER BY klasse"); // returns data[n][data]

        $forms = array();

        foreach ($data as $item)
        {
            array_push($forms, $item[0]);
        }
		
		return $forms;

    }
	
	
	/**
	* connect teacher with form
	* @param array(int)  with teacher Ids
	* @param string $form class
     * TODO / FIXME : don't delete everything but instead only delete the deleted ones .... may be more database queries but a lot more nicer... my heart is bleeding...
	*/
	public function setTeacherToForm($teacher,$form){
		//check if this connection exists
		self::$connection->straightQuery("DELETE FROM unterricht WHERE klasse='$form'");
		if(isset($teacher)) {
		foreach($teacher as $t){
			self::$connection->insertValues("INSERT INTO unterricht (`id`,`lid`,`klasse`) VALUES ('','$t','$form')");
			}	
		}
		
		
	}
	
	/**
	* get teachers in form
	* @param string $form
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
