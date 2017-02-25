<?php

class CoverLesson{

public $primaryKey;
public $tag;
public $datum;//Format Tag DD.MM.YYYY
public $timestampDatum;// Format YYYYMMDD
public $vTeacher;//String
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
public $emailed=0;
public $changedEntry=0;
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
$this->operateInsert();

} 

/*
*Erstelle das Objekt aus den Daten der DB
*/
public function constructFromDB($data){
$this->primaryKey = $data['vNr'];
$this->tag = $data['tag'];
$this->datum = $this->makeCompleteDate($data['datum']);
$this->timestampDatum = $data['datum'];
//vertretender Lehrer als Objekt
//$vTeacher = new Teacher($this->getTeacherIdByUntisName($data['vLehrer']),$this->connection);
$rawData = array("untisName" => $data['vLehrer']);

$constructData = Model::getInstance()->getTeacherDataByUntisName($data['vLehrer']);

	$this->vTeacherObject = new Teacher($constructData['email'],$constructData['id'],$rawData);
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
$this->eTeacherObject=$eTeacher;
}
$this->eFach = $data['eFach'];
$this->kommentar = $data['kommentar'];
$this->id = $data['id'];
}

/*
*bereite den Eintrag in die DB vor (prüfe, was gemacht werden muss,  Neueintrag, Änderung etc)
*/
private function operateInsert(){
	Model::getInstance()->InsertCoverLesson(); //to be recoded from old code
	
/*	OLD CODE
	
//Prüfe ob dieser Eintrag bereits vorhanden ist
$data=$this->connection->selectAssociativeValues("SELECT * FROM vplanData WHERE id=\"$this->id\" ");
if (count($data)>0){
	$DBCoverL = new CoverLesson($this->connection);
	$DBCoverL->ConstructFromDB($data[0]);
	$pk=$DBCoverL->primaryKey;
	$this->connection->straightQuery("UPDATE vplanData SET aktiv=true,tag=$this->tag,stand=\"$this->stand\" WHERE vNr=$pk");
	//prüfe ob Kommentar geaendert ist
	if ($this->kommentar<>$DBCoverL->kommentar){
		$k=$this->kommentar;
		//Komentar updaten
		$this->connection->straightQuery("UPDATE vplanData SET kommentar=\"$k\",aktiv=true WHERE vNr=$pk");
		}
	if($this->changedEntry==1){
		//emailedFlag entfernen und alle Felder Updaten
		$this->emailed="";
		$this->aktiv=true;
		$this->updateAll($pk);
		
		}	
	}
else{
	//Eintrag in Datenbank
	$this->aktiv=true;
	$this->writeData();
	}
*/
}

/*
*Formatierung der Daten (führende Null bei Klassennamen, Leerzeichen entfernen etc)
*/
private function trimClassString($clStrg){
//Klassenstring anpassen
$kArr=explode(",",$clStrg);
for ($x=0;$x<count($kArr);$x++) {
$kArr[$x]=trim($kArr[$x],$character_mask = " \t\n\r\0\x0B");
if(strlen($kArr[$x])<3 && $kArr[$x][0]<>"K" && $kArr[$x][0]<>"A") {$kArr[$x]='0'.$kArr[$x];}
}

$klassenString="";
for ($x=0;$x<count($kArr);$x++) {
if($x==0) {$klassenstring=$kArr[$x];}
else {$klassenstring=$klassenstring.','.$kArr[$x];}
}
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
*Trage Daten in DB ein
*/
public function writeData(){
//Einfügen eines neuen Datensatzes
Model::getInstance()->writeCoverLessonToDB(); //to be recoded from old code

/* OLD CODE
$this->connection->insertValues("INSERT into vplanData (`vNr`,`tag`,`datum`,`vLehrer`,`klassen`,`stunde`,`fach`,`raum`,`eLehrer`,`eFach`,`kommentar`,`id`,`aktiv`,`stand` )
 VALUES ('','$this->tag','$this->datum','$this->vTeacher','$this->klassen','$this->stunde','$this->vFach','$this->vRaum','$this->eTeacherKurz','$this->eFach','$this->kommentar','$this->id','$this->aktiv','$this->stand')");
 */
}

/*
*Aktualisiere bestehenden Datensatz
*/
private function updateAll($vNr){
	Model::getInstance()->updateAllCoverLesson(); //to be recoded from old code
	/* OLD CODE
	$this->connection->straightQuery("UPDATE vplanData SET tag=$this->tag,datum=\"$this->datum\",vlehrer=\"$this->vTeacher\",
		klassen=\"$this->klassen\",stunde=\"$this->stunde\",fach=\"$this->vFach\",raum=\"$this->vRaum\",
		eLehrer=\"$this->eTeacherKurz\",eFach=\"$this->eFach\",kommentar=\"$this->kommentar\",id=\"$this->id\",aktiv=$this->aktiv,
		emailed=\"$this->emailed\",stand=\"$this->stand\" WHERE vNr=$vNr");	
	*/
}


/*
*Ermittle Primary key zu Lehrer Untis Name
*/
private function getTeacherIdByUntisName($untisName){
	//check if still needed 
/* OLD CODE	
$data=$this->connection->selectValues("SELECT lNr FROM lehrerdata WHERE untisName=\"$untisName\" ");
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
$data=$this->connection->selectValues("SELECT lNr FROM lehrerdata WHERE kurz=\"$kurz\" ");
if(count($data)>0){
	return $data[0][0];
	}
else{
	return $kurz;
	}
*/
}


/*
*Datumsstring umrechnen
* @param $date String im Format YYYYMMDD
*/
private function makeCompleteDate($date){
	//USE FROM MODEL
/* OLD CODE	
$year=$date[0].$date[1].$date[2].$date[3];
$month=$date[4].$date[5];
$day=$date[6].$date[7];
$wochentage = array ('Sonntag','Montag','Dienstag','Mittwoch','Donnerstag','Freitag','Samstag');
$datum = getdate(mktime ( 0,0,0, $month, $day, $year));
$wochentag = $datum['wday'];

$completeDate=$wochentage[$wochentag].", den ".$day.".".$month.".".$year;
return $completeDate;
*/
}



}

?>