<?php

/**
 * Database Connection Klasse
 * erstellt die Verbindung zur Datenbank
 */
class Connection
{
    /**
     * @var string database ip
     */
    private $server;
    /**
     * @var string database user
     */
    private $user;
    /**
     * @var string database password
     */
    private $pass;
    /**
     * @var mysqli database connection instance
     */
    private $connID;
    /**
     * @var string database name
     */
    private $database;


    /**
     * Konstruktor der automatisch die Datenbankverbindung
     * bei Instanzierung aufbaut
     */
    public function __construct()
    {
        $this->getCredentials();
        $this->connect();
    }


    /**
     * Ermittelt die Serververbindung, benutzer und Passwort zur Basisdatenbank
     */
    private function getCredentials()
    {
        $f = fopen("cfg.ini", "r");
        while (!feof($f)) {
            $line = fgets($f);
            $larr = explode("=", $line);
            switch ($larr[0]) {
                case "SERVER":
                    $this->server = trim($larr[1]);
                    break;
                case "USER":
                    $this->user = trim($larr[1]);
                    break;
                case "PASS":
                    $this->pass = trim($larr[1]);
                    break;
                case "DB":
                    $this->database = trim($larr[1]);
                    break;
            }
        }

        fclose($f);
    }


    /**
     * Verbindet mit der jeweils benutzten Datenbank
     */
    private function connect()
    {

        $reporting = error_reporting(0);
        $mysqli = $this->connID = new mysqli($this->server, $this->user, $this->pass, $this->database);
        error_reporting($reporting);

        if ($mysqli->connect_errno) {
            printf("Connection to database failed: %s\n", $mysqli->connect_error);
            ChromePhp::error("Connection to database failed: " . $mysqli->connect_error);
            exit();
        }

        ChromePhp::info("Connection to database " . $this->user . "@" . $this->server . "/" . $this->database . " successful!");

        mysqli_set_charset($mysqli, 'utf8');
    }


    /**
     * universelle Methode zur Rückgabe von Abfrageergebnissen
     * @param string $query SQL Abfrage
     * @return array[][] welches im ersten Index die Zeile, im Zweiten Index die Spalte bezeichnet
     *  zur Abfrage kann also jeweils im zweiten Index auf die einzelnen Ergebnisse in angegebener
     *  Reihenfolge zurückgegriffen werden
     */
    public function selectValues($query)
    {
        $mysqli = $this->connID;
        $result = $mysqli->query($query) or die($mysqli->error . "</br></br>" . $query . "</br></br>" . $this->getCaller());
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
     * @param $query String SQL Query
     * @return array[Feldname][]
     */
    public function selectAssociativeValues($query)
    {
        $mysqli = $this->connID;
        $result = $mysqli->query($query) or die($mysqli->error);
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
     * Universelle Methode zum Einfügen von Daten
     * @param string $query SQL Abfrage
     * @return int Insert ID
     *
     */
    public function insertValues($query)
    {
        $mysqli = $this->connID;
        $mysqli->query($query) or die($mysqli->error);
        return $mysqli->insert_id;
    }


    /**
     * Führt eine SQL Query durch
     * @param $query String SQL Query
     */
    public function straightQuery($query)
    {

        $this->connID->query($query);
    }


    /**
     * Schließt die Datenbank Verbindung
     */
    public function close()
    {
        mysqli_close($this->connID);
    }


    /**
     * Gibt die genutzte mysqli Klasse zurück
     * @return mysqli
     */
    public function getConnection()
    {
        return $this->connID;
    }


    /**
     * Escapes special characters in a string for use in an SQL statement, taking into account the current charset of the connection
     * @param $string
     * @return string
     */
    public function escape_string($string)
    {
        return $this->connID->real_escape_string($string);
    }

    /**
     * @return string location the caller method was called from
     */
    private function getCaller()
    {
        $info = debug_backtrace();
        $file = $info[1]['file'];
        $line = $info[1]['line'];
        $method = $info[2]['function'];
        return "$file:$line / $method()";

    }


}

?>
