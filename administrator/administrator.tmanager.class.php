<?php namespace administrator;

class TManager{

/**
*Klasse zur Verarbeitung von Terminobjekten
*/
private $connection;
private $monate=null;//Array("mnum"=>string,"mstring"=>string,"jahr"=>int) der Monate mit Terminen 


	
/**
*Eintrag aller Termine in die Datenbank
*@param $termine Array(Terminobjekt)
*/
public function addToDB($termine){
	//check with current Events!!!!
	//Tabelle leeren
	//$this->connection->straightQuery("TRUNCATE termine");
	foreach($termine as $t){
		$query="INSERT INTO termine (`tNr`,`typ`,`start`,`ende`,`staff`) VALUES ('','$t->typ','$t->start','$t->ende','$t->staff')	";
		//echo $query."<br>";
		//$this->connection->insertValues($query);
		}	
	}	


/**
*Termine aus Datenbank auslesen
*@param $includeStaff Boolean
*@return Array(Terminobjekt)
*/
public function readFromDB($includeStaff){
	/*$includeStaff ? $query="SELECT typ,start,ende,staff FROM termine ORDER BY start" : $query="SELECT typ,start,ende,staff FROM termine WHERE staff=0 ORDER BY start" ;
	$data=$this->connection->selectValues($query);
	foreach ($data as $d){
		$termin=new Termin();
		$termin->createFromDB($d);
		$this->makeMonthsArray($termin->monatNum,$termin->monat,$termin->jahr);
		$termine[]=$termin->createFromDB($d);
		}	
	return $termine;*/
	}

/**
*Monatsarray mit Terminen erstellen
*@param string Monat als Zahl
*@param string Monat als Text
*@param string jahr
*/
private function makeMonthsArray($monatZahl,$monat,$jahr){
	$noAdd=false;
	if(isset($this->monate)){
		 foreach ($this->monate as $m){
		 if($m["mnum"]==$monatZahl) {
				$noAdd=true;
			}
		}
	}
	if(!$noAdd) $this->monate[]=array("mnum"=>$monatZahl,"mstring"=>$monat,"jahr"=>$jahr);
}

/**
*Monatarray abrufen
*@return array(string) monate*/
public function getMonths(){
	return $this->monate;
}
	
	
/**
*Eintrag aller Termins in eine ics-Datei
*@param $file String Dateiname
*@param $termine Array(Terminobjekt)
*/
public function createICS($file,$termine){
	$f=fopen($file,"w");
	fwrite($f,"BEGIN:VCALENDAR\r\n");
	foreach($termine as$t){
		fwrite($f,"BEGIN:VEVENT\r\n");
		//if ($mail==true") {fwrite($f,'ATTENDEE;CN="Kollegium (lehrer@suso.schulen.konstanz.de)";RSVP=TRUE:mailto:lehrer@suso.schulen.konstanz.de');}
		$entryTextStart="DTSTART:";
		$entryTextEnd="DTEND:";
		if (strlen($t->start)<9){
			//keine Zeitangabe, also ganztägiger Termin
			$entryTextStart="DTSTART;VALUE=DATE:";
			$entryTextEnd="DTEND;VALUE=DATE:";
			}
		fwrite($f,$entryTextStart.$t->start."\r\n");
		fwrite($f,$entryTextEnd.$t->ende."\r\n");
		//if($this->ort) {fwrite($f,"LOCATION:".$this->ort."\r\n");}
		fwrite($f,"SUMMARY;LANGUAGE=de:".$t->typ."\r\n");
		/*fwrite($f,'X-ALT-DESC;FMTTYPE=text/html:<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2//EN">\n<HTML>\n
				<HEAD>\n<TITLE></TITLE>\n</HEAD>\n<BODY>\n\n<P DIR=LTR><SPAN LANG="de"></SPAN></P>\n\n</BODY>'.$text.'</HTML>');
			*/
		fwrite($f,"END:VEVENT\r\n");
	
		}
	fwrite($f,"END:VCALENDAR");
	fclose($f);
	}	
	
	
}

?>