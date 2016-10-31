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
        if(self::$connection == null)
            self::$connection = new Connection();

    }

    static function getInstance()
    {
        return self::$model == null ? self::$model = new Model() : self::$model;
    }

    /**
	*read datafields from database
	*@param bool student
	*@return array datafield names
	*/
	public function readDBFields($student){
		($student) ? $table="schueler" : $table="lehrer";
		$data=self::$connection->selectFieldNames("SELECT * FROM ".$table);
		return $data;
		
	}
	
	
	
	/**
     * @param $userName
     * @param $password
     * @return bool user exists in database and password is equal with the one in the database
     */
    public function passwordValidate($userName, $password)
    {

        $userName = self::$connection->escape_string($userName);
        //$password = self::$connection->escape_string($userName);

        $data = self::$connection->selectAssociativeValues("SELECT password_hash from user WHERE username='$userName'");

        if($data == null)
            return false;


        $data = $data[0];

        $pwd_hash = $data['password_hash'];


        return password_verify($password, $pwd_hash);
    }
	
	/**
     * @param string $userName
     * @return int userId
     */
    public function usernameGetId($userName)
    {
        $userName = self::$connection->escape_string($userName);
        $data = self::$connection->selectValues("SELECT id FROM user WHERE username='$userName'");

        if($data == null)
            return null;

        return $data[0][0];
    }
	
	 /**
     * @param int $userid
     * @return int userType [0 - admin; 1 - parent; 2 - teacher]
     */
    public function userGetType($userid)
    {
        $data = self::$connection->selectValues("SELECT user_type FROM user WHERE id=" .$userid);

        if(!isset($data[0]))
            return null;

        return intval($data[0][0]);

    }

	
	/**
	*check if data exist in database
	*@param int id
	*@param bool
	*/
	public function checkDBData($student,$id){
			($student) ? $table="schueler" : $table="lehrer";
			$data=self::$connection->selectValues("SELECT id FROM $table where id=$id");
			if(count($data)>0){
				return true;
				}
			else{
				return false;
			}
	}
	
	
	/**
	*update data
	*@param bool 
	*@param int 
	*@param array
	*/
	public function updateData($student,$id,$line){
		($student) ? $table="schueler" : $table="lehrer";
		$string=null;
		foreach($line as $key=>$value){
		if(isset($string)) {$string=$string.",$key=\"$value\" ";} else {$string="$key=\"$value\" ";}
		}
		$string=$string.",upd=1 WHERE id=$id";
		$string="UPDATE $table SET ".$string;
		self::$connection->straightQuery($string);
		}
	
	/**
	*insert data
	*@param bool 
	*@param array
	*/
	public function insertData($student,$line){
		($student) ? $table="schueler" : $table="lehrer";
		$fieldstring=null;
		$valuestring=null;
		foreach($line as $key=>$value){
		$key=trim($key);
		$value=trim($value);
		if(isset($fieldstring)) {$fieldstring=$fieldstring.",`$key`";} else {$fieldstring="`$key`";}
		if(isset($valuestring)) {$valuestring=$valuestring.",'$value'";} else {$valuestring="'$value'";}
		}
		$fieldstring=$fieldstring.",`upd`"; 
		$valuestring=$valuestring.",'1'";
		$string="INSERT INTO $table (".$fieldstring.") VALUES (".$valuestring.")";
		self::$connection->insertValues($string);
		
		}
	
	/**
	*delete unused data from DB
	*@param bool
	*/
	public function deleteDataFromDB($student){
		$toDelete=0;
		($student) ? $table="schueler" : $table="lehrer";
		$data=self::$connection->selectValues("SELECT id FROM $table WHERE upd=0");
		$toDelete=count($data);
		self::$connection->straightQuery("DELETE FROM $table WHERE upd=0");
		return $toDelete;
	}
	
	/**
	*set update status to zero
	*/
	public function setUpdateStatusZero($student){
		($student) ? $table="schueler" : $table="lehrer";
		self::$connection->straightQuery("UPDATE $table SET upd=0");
	}
	
}


?>