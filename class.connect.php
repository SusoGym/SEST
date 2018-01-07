<?php

require_once("MySQLException.php");

/**
 * Database Connection Klasse
 * erstellt die Verbindung zur Datenbank
 */
class Connection {
    
    /**
     * @var string configuration file
     */
    public static $configFile = "cfg.ini";
    /**
     * @var array parameters stored in cfg.ini
     */
    private $iniParams = array();
    /**
     * @var \mysqli database connection instance
     */
    
    private $connID;
    
    
    /**
     * Konstruktor der automatisch die Datenbankverbindung
     * bei Instanzierung aufbaut
     */
    public function __construct() {
        $this->getCredentials();
        $this->connect();
    }
    
    
    /**
     * Ermittelt die Serververbindung, benutzer und Passwort zur Basisdatenbank
     */
    private function getCredentials() {
        $f = fopen(self::$configFile, "r");
        while (!feof($f)) {
            $line = fgets($f);
            $larr = explode("=", $line);
            if(sizeof($larr) < 2){continue;}
            $this->iniParams[strtolower(trim($larr[0]))] = trim($larr[1]);
        }
        fclose($f);
    }
    
    /**
     * returns values from ini
     *
     * @return array(string)
     */
    public function getIniParams() {
        return $this->iniParams;
    }
    
    
    /**
     * Verbindet mit der jeweils benutzten Datenbank
     */
    private function connect() {
        
        $reporting = error_reporting(0);
        
        $params = $this->getIniParams();
        
        ChromePhp::info("Connecting to " . $params['user'] . "@" . $params['server'] . ":" . $params['db']);
        $mysqli = $this->connID = new \mysqli($params['server'], $params['user'], $params['pass'], $params['db']);
        error_reporting($reporting);
        
        if ($mysqli->connect_errno) {
            printf("Connection to database failed: %s\n", $mysqli->connect_error);
            ChromePhp::error("Connection to database failed: " . $mysqli->connect_error);
            exit();
        }
        
        //ChromePhp::logSQL("Connection to database " . $this->user . "@" . $this->server . "/" . $this->database . " successful!");
        
        mysqli_set_charset($mysqli, 'utf8');
    }
    
    
    /**
     * universelle Methode zur Rückgabe von Abfrageergebnissen
     *
     * @param string $query SQL Abfrage
     *
     * @return array[][] welches im ersten Index die Zeile, im Zweiten Index die Spalte bezeichnet
     *  zur Abfrage kann also jeweils im zweiten Index auf die einzelnen Ergebnisse in angegebener
     *  Reihenfolge zurückgegriffen werden
     * @throws MySQLException
     */
    public function selectValues($query) {
        ChromePhp::logSQL("Selecting values: \"$query\" from " . $this->getCaller());
        $mysqli = $this->connID;
        $result = $mysqli->query($query);
        
        if ($result === false) {
            throw new MySQLException($mysqli, $query);
        }
        
        $value = null;
        $anz = $result->field_count;
        $valCount = 0;
        
        while ($row = $result->fetch_array(MYSQLI_NUM)) {
            for ($x = 0; $x < $anz; $x++) {
                $value[$valCount][$x] = $row[$x];
            }
            $valCount++;
        }
        $result->free();
        
        return $value;
    }
    
    
    /**
     * Gibt assoziative Arrays zurück
     * Indizes mit Feldnamen
     *
     * @param $query String SQL Query
     *
     * @return array[Feldname][] | null
     * @throws MySQLException
     */
    public function selectAssociativeValues($query) {
        ChromePhp::logSQL("Selecting values:  \"$query\" from " . $this->getCaller());
        $mysqli = $this->connID;
        $result = $mysqli->query($query);
        
        if ($result === false) {
            throw new MySQLException($mysqli, $query);
        }
        
        $assocValue = null;
        $fieldName = null;
        $anz = $result->field_count;
        $valCount = 0;
        
        while ($row = $result->fetch_array(MYSQLI_NUM)) {
            for ($x = 0; $x < $anz; $x++) {
                $fieldInfo = $result->fetch_field_direct($x);
                $assocValue[$valCount][$fieldInfo->name] = $row[$x];
            }
            $valCount++;
        }
        
        $result->free();
        
        return $assocValue;
    }
    
    /**
     * gibt ein Array mit den Feldnamen zurück
     *
     * @param string $query
     *
     * @return array
     */
    public function selectFieldNames($query) {
        ChromePhp::logSQL("Selecting FieldNames:  \"$query\" from " . $this->getCaller());
        $fieldNames = array();
        $mysqli = $this->connID;
        if ($result = $mysqli->query($query)) {
            $finfo = $result->fetch_fields();
            foreach ($finfo as $f) {
                $fieldNames[] = $f->name;
            }
            
            return $fieldNames;
        } else {
            return null;
        }
    }
    
    
    /**
     * Universelle Methode zum Einfügen von Daten
     *
     * @param string $query SQL Abfrage
     *
     * @return int Insert ID
     * @throws MySQLException
     */
    public function insertValues($query) {
        ChromePhp::logSQL("Inserting:  \"$query\" from " . $this->getCaller());
        $mysqli = $this->connID;
        $result = $mysqli->query($query);
        
        if ($result === false) {
            throw new MySQLException($mysqli, $query);
        }
        
        return $mysqli->insert_id;
    }
    
    
    /**
     * Führt eine SQL Query durch
     *
     * @param $query String SQL Query
     *
     * @throws MySQLException
     */
    function straightQuery($query) {
        ChromePhp::logSQL("Query:  \"$query\" from " . $this->getCaller());
        
        $mysqli = $this->connID;
        $result = $mysqli->query($query);
        
        if ($result === false) {
            throw new MySQLException($mysqli, $query);
        }
    }
    
    /**
     * Führt eine multi SQL Query durch (Statement1 ; Statement2 ; ...)
     *
     * @param $query
     *
     * @throws MySQLException
     */
    function straightMultiQuery($query) {
        ChromePhp::logSQL("MultiQuery:  \"$query\" from " . $this->getCaller());
        $mysqli = $this->connID;
        $result = $mysqli->multi_query($query);
        
        if ($result === false) {
            throw new MySQLException($mysqli, $query);
        }
        
        while ($mysqli->more_results() && $mysqli->next_result()) ;
    }
    
    /**
     * Schließt die Datenbank Verbindung
     */
    public function close() {
        mysqli_close($this->connID);
    }
    
    
    /**
     * Gibt die genutzte mysqli Klasse zurück
     *
     * @return \mysqli
     */
    public function getConnection() {
        return $this->connID;
    }
    
    
    /**
     * Escapes special characters in a string for use in an SQL statement, taking into account the current charset of the connection
     *
     * @param $string
     *
     * @return string
     */
    function escape_string($string) {
        return $this->connID->real_escape_string($string);
    }
    
    /**
     * Escapes special characters in a string for use in an SQL statement, taking into account the current charset of the connection
     * Will change the given String
     *
     * @param $string
     */
    function escape_stringDirect(&...$string) {
        foreach ($string as &$str) {
            $str = $this->escape_string($str);
        }
    }
    
    /**
     * @return string location the caller method was called from
     */
    function getCaller() {
        $info = debug_backtrace();
        $file = $info[1]['file'];
        $line = $info[1]['line'];
        $method = $info[2]['function'];
        
        return "$file:$line / $method()";
        
    }
    
    
}


?>
