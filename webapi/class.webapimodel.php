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
	****************webapi functions****************
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

}

?>