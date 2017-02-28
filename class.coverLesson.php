<?php

class CoverLesson{

//GETTERS SHOULD BE IMPLEMENTED

public $primaryKey;
public $tag;
public $datum;//Format Tag DD.MM.YYYY
public $timestampDatum;// Format YYYYMMDD
//public $vTeacher;//String
public $vTeacherObject;//Teacher
public $stunde;
public $klassen;
public $vFach;
public $vRaum;
public $eTeacherKurz;//String
public $eTeacherObject;//Teacher
public $eFach;
public $eRaum;
public $kommentar;
public $id;
public $aktiv;//boolean
public $emailed = 0;
public $changedEntry = 0;
public $stand;//Datum der letzten Änderung
private $connection;




/*
*Erstelle ein Objekt aus dem per Post gesendeten String
*@param text String
*/
public function constructFromPOST($text){
$text = str_replace("'","",$text);
$ta = explode(";",$text);
$v = array();
foreach($ta as $t){
	$v[] = $t;
	}
$this->tag = $v[0];
$this->datum = $v[1];
$this->vTeacher = $v[2];
$this->klassen = $this->trimClassString($v[3]);
$this->stunde = $this->trimPeriodString($v[4]);
$this->vFach = $v[5];
$this->vRaum = $v[6];
$this->eTeacherKurz = $v[7];
$this->eFach = $v[8];
$this->kommentar = $v[9];
if(isset($v[10])) {$this->changedEntry = $v[10];}
$this->stand = $v[11];
$this->id = $this->makeId();
} 

/*
*Erstelle das Objekt aus den Daten der DB
*/
public function constructFromDB($data){
$this->primaryKey = $data['vNr'];
$this->tag = $data['tag'];
$this->datum = Model::getInstance()->makeCompleteDate($data['datum']);
$this->timestampDatum = $data['datum'];
//vertretender Lehrer als Objekt
//$vTeacher = new Teacher($this->getTeacherIdByUntisName($data['vLehrer']),$this->connection);
$rawData = array("untisName" => $data['vLehrer']);

$constructData = Model::getInstance()->getTeacherDataByUntisName($data['vLehrer']);

//$this->vTeacherObject = new Teacher($constructData['email'],$constructData['id'],$rawData);
$this->vTeacherObject = new Teacher(null,$constructData['id'],$rawData);
$this->vTeacherObject->getData();


$this->klassen = $data['klassen'];
$this->stunde = $data['stunde'];
$this->vFach = $data['fach'];
$this->vRaum = $data['raum'];
//zu vertretender Lehrer als Objekt
if($this->vTeacherObject->getShortName() == $data['eLehrer']){
//Vertreter und zu Vertretender sind identisch -> z.B. bei Raumänderung
$this->eTeacherObject = $this->vTeacherObject;
}
else{
$rawData = array("shortName" => $data['eLehrer']);
$constructData = Model::getInstance()->getTeacherDataByShortName($data['eLehrer']);
$eTeacher = new Teacher($constructData['email'],$constructData['id'],$rawData);
$eTeacher->getData();
$this->eTeacherObject = $eTeacher;
}
$this->eFach = $data['eFach'];
$this->kommentar = $data['kommentar'];
$this->id = $data['id'];
}




/**
* Formatierung der Daten (führende Null bei Klassennamen, Leerzeichen entfernen etc)
* @param String
* @return String
*/
private function trimClassString($clStrg){
//Klassenstring anpassen
$kArr=explode(",",$clStrg);
for ($x = 0;$x<count($kArr);$x++) {
$kArr[$x] = trim($kArr[$x],$character_mask = " \t\n\r\0\x0B");

if(strlen($kArr[$x])<3 && $kArr[$x][0]<>"K" && $kArr[$x][0]<>"A") {$kArr[$x]='0'.$kArr[$x];}
}
$klassenString = "";
for ($x = 0;$x<count($kArr);$x++) {
if($x == 0) {$klassenstring = $kArr[$x];}
else {$klassenstring = $klassenstring.','.$kArr[$x];}
}
$search = array('K1','K2');
$replace = array('11','12');
$klassenstring = str_replace($search,$replace,$klassenstring);
return $klassenstring;
}

/*
*Formatierung der Daten entferne Leerzeichen aus dem String der die betroffene Stunde anzeigt
*/
private function trimperiodString($pString){
//Stundenstring
return str_replace(" ","",$pString);
}


/**
*Erstlle eine ID zum Eintrag in die DB mittels welcher die Existenz einse Eintrags überprüft wird
*@return string
*/
private function makeId(){
return $this->datum.$this->vTeacher.$this->klassen.$this->stunde.$this->eTeacherKurz;
}




/*
*Ermittle Primary key zu Lehrer Untis Name
*/
private function getTeacherIdByUntisName($untisName){
	//check if still needed 
/* OLD CODE	
$data = $this->connection->selectValues("SELECT lNr FROM lehrerdata WHERE untisName=\"$untisName\" ");
if(count($data)>0){
	return $data[0][0];
	}
else{
	return $untisName;//z.B. "---" oder "selbst"
	}
*/
}

/*
*Ermittle Primary key zu Lehrer Kurzzeichen
*/
private function getTeacherIdByKurz($kurz){
	//check if still needed
/* OLD CODE
$data = $this->connection->selectValues("SELECT lNr FROM lehrerdata WHERE kurz=\"$kurz\" ");
if(count($data)>0){
	return $data[0][0];
	}
else{
	return $kurz;
	}
*/
}






}

?>