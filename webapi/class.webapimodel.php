<?php
/***************************************
****************************************
**Model Class only used in WebAPIMode***
***************************************/


class WebApiModel extends \Model {




/**
*Konstruktor
*/
public function __construct() {
parent::__construct();
}

	
/***********************************************
*****************webapi functions****************
***********************************************/

	
	/**
	* check token at web api authentication
	* @param string
	* @return string*/

	public function checkTokenAuth($token) {
		$data = self::$connection->selectValues('SELECT customer FROM api_token WHERE token ="'.$token.'"');
		if (!empty($data) ){
				return $data[0][0];
			} else {
				return null;
			}
	}


	/**
     *Eintrag aller Termine in die Datenbank
     *
     * @param $termine JSON String
     */
    public function addEventsToDB($termine) {
        //Tabelle leeren
        self::$connection->straightQuery("TRUNCATE termine_new");
        foreach ($termine as $t) {
			$staffOnly = ($t[3] == 'L') ? 1 : 0;
            $query = "INSERT INTO termine_new (`tNr`,`typ`,`start`,`ende`,`staff`) VALUES ('','$t[0]','$t[1]','$t[2]','$staffOnly')	";
            self::$connection->insertValues($query);
        }
	}
	
	/**
	* returns all users without registered children
	* @return array()
	*/
	public function getUsersWithoutKids() {
		$users = array();
		$data = self::$connection->selectValues("SELECT email,name,vorname,registered,user.id,eltern.id 
		FROM user,eltern 
		WHERE user.id = eltern.userid order by registered");
		
		$now = new DateTime( date('Y-m-d H:i:s') );
		
		if (!empty($data) ) {
		foreach ($data as $d) {
				$then = new DateTime($d[3]);
				$interval = $then->diff($now)->format("%a");
				if($interval > 90 ) {//delete those where registration time is more than 90 days gone
						$del = true;
					} else {
						$del = false;
					}
				$users[] = array("name"=>$d[1],
				"vorname"=>$d[2],
				"mail"=>$d[0],
				"registered"=>$d[3],
				"id"=>$d[4],
				"eid"=>$d[5],
				"todelete"=>$del);
				}		
			}

		$unused = array();
		foreach ($users as $u) {
			$data = self::$connection->selectValues("SELECT id FROM schueler WHERE eid = ".$u['eid']." OR eid2= ".$u['eid']);
			if (!$data[0][0] ) {
				array_push($unused,$u);
				}
			}
	
		return $unused;
	}

	/**
	 * get all users who have registered but not verified the registration
	 * @return array
	 */
	public function getUsersWithoutConfirmedRegistration() {
		$unconfirmedUsers = array();
		$data = self::$connection->selectValues("SELECT email,name,vorname,registered,user.id,eltern.id 
		FROM user,eltern 
		WHERE user.id = eltern.userid 
		AND confirm_token is not null order by registered");
		$now = date('Y-m-d H:i:s');
		$now = new DateTime($now);
		if (!empty($data) ) {
			foreach ($data as $d) {
					$then = new DateTime($d[3]);
					$interval = $then->diff($now)->format("%a");
					if($interval >1 ) {//delete those who have not confirmed their registration after morde than 1 day
					$unconfirmedUsers[] = array("name"=>$d[1],"vorname"=>$d[2],"mail"=>$d[0],
					"registered"=>$d[3],"id"=>$d[4],"eid"=>$d[5]);	
					}
					
					}		
				}
		return $unconfirmedUsers;

	}

	/**
	 * deletes users from users and eltern table
	 * users are only those who could have registered themselves
	 * @param int id
	 * @param int eid
	 */
	public function deleteUsers($id,$eid){
		$query= "DELETE FROM users WHERE id =".$id;
		$query2 = "DELETE FROM eltern WHERE id=".$eid;
		//self::$connection->straightQuery($query);
		//self::$connection->straightQuery($query2);
	}

}

?>