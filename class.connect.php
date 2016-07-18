<?php
/**
* Database Connection Klasse
* erstellt die Verbindung zur Datenbank
*/
class Connection
{
private $server;
private $user;
private $pass;
private $connID;
private $database;
/*
* Konstruktor der automatisch die Datenbankverbindung
* bei Instanzierung aufbaut
* @param String Datenbankname
*
*/
public function __construct($db)
{
$this->getCredentials();	
$this->connect($db);
}
/**
* Ermittelt die Serververbindung, benutzer und Passwort zur Basisdatenbank
*/
private function getCredentials(){
$f=fopen("cfg.ini","r");
while(!feof($f))
{
$credentials=array();
$line=fgets($f);
$larr=explode("=",$line);
switch($larr[0]){
case "SERVER": $this->server=trim($larr[1]);break;
case "USER": $this->user=trim($larr[1]);break;
case "PASS": $this->pass=trim($larr[1]);break;
case "DB": $this->database=trim($larr[1]);break;

}
}
fclose($f);
}

/**
* Verbindet mit der jeweils benutzten Datenbank
*/
private function connect($database)
{
$mysqli=new mysqli($this -> server,$this -> user,$this -> pass,$this -> database);
$this->connID=$mysqli;
if ($mysqli->connect_errno) {
  printf("Connect failed: %s\n", $mysqli->connect_error);
  exit();
}

mysqli_set_charset($mysqli, 'utf8');
}
/*
* universelle Methode zur Rückgabe von Abfrageergebnissen
* @param string SQL Abfrage
* @return Array[][] welches im ersten Index die Zeile, im Zweiten Index die Spalte bezeichnet
*  zur Abfrage kann also jeweils im zweiten Index auf die einzelnen Ergebnisse in angegebener
*  Reihenfolge zurückgegriffen werden
*/
public function selectValues($query)
{
//Objektorientierte Version der Abfrage
$mysqli=$this->connID;
$result=$mysqli->query($query) or die($mysqli->error);
$value=null;
$anz=$result->field_count;
$valCount=0;
while ($row=$result->fetch_array(MYSQLI_NUM))
{
  for ($x=0;$x<$anz;$x++)
  {
    $value[$valCount][$x]=$row[$x];
  }
  $valCount++;
}
$result->free();
return $value;
}
/**
* Gibt assoziative Arrays zurück
* Indizes mit Feldnamen
* @param $quer String SQL Query
* @return Array[Feldname][]
*/
public function selectAssociativeValues($query)
{
$mysqli=$this->connID;
$result=$mysqli->query($query) or die($mysqli->error);
$assocValue=null;
$fieldName=null;
$anz=$result->field_count;
$valCount=0;
while ($row=$result->fetch_array(MYSQLI_NUM))
{
for ($x=0;$x<$anz;$x++)
{
$fieldInfo= $result->fetch_field_direct($x);
$assocValue[$valCount][$fieldInfo->name]=$row[$x];
}
$valCount++;
}
$result->free();
return $assocValue;
}
/*
* Universelle Methode zum Einfügen von Daten
* @param string SQL Abfrage
* @return int Insert ID
*
*/
public function insertValues($query)
{
//Ausführen von INSERT_QUERY
$mysqli=$this->connID;
$mysqli->query($query);
return $mysqli->insert_id;
}
/**
* Führt eine SQL Query durch
* @param $query String SQL Query
*/
function straightQuery($query)
{
$mysqli=$this->connID;
$mysqli->query($query);
}
/**
* Schließt die Datenbank Verbindung
*/
public function close(){
$mysqli=$this->connID;
//mysqli_close($mysqli);
}
}
?>
