<?php

/*
*Klasse Termine
*/

class Termin {
    
    public $typ;
    public $start;
    public $ende;
    public $staff = false;//boolean - Lehrertermine true
    public $monat;//Monat als Text
    public $monatNum;//Monat als Zahl
    public $jahr;
    public $sday = null;
    public $stime = null;
    public $eday = null;
    public $etime = null;
    public $sweekday = null;
    public $eweekday = null;
    public $sTimeStamp = null; //nur der Datumstimestamp yyyymmdd
    
    //private $text=null;
    //private $ort=null;
    //private $mail=false;//wenn ics-Datei für kalenderadmin erstellt wird true
    
    /**
     *Konstruktor nach übergabe einer Zeile der Quelldatei
     *
     * @param $data Array
     */
    
    public function createFromCSV($data) {
        if ($data[8] == "L") {
            $this->staff = true;
        }
        $this->typ = $data[0];
        $this->start = $this->makeDate($data[2]);
        if ($data[4]) {
            //mehrtägig
            $this->start = $this->start;//."T000000";
            $this->ende = $this->makeDate($data[4]);
            $this->ende = $this->ende;//."T235959";
        } else {
            //eintägiger Termine
            $this->ende = $this->start;
            if ($data[7]) {
                //Endzeit angegeben
                $this->start = $this->start . $this->makeTime($data[5]);
                $this->ende = $this->ende . $this->makeTime($data[7]);
            } else if ($data[5]) {
                //nur Startzeit angegeben
                $this->start = $this->start . $this->makeTime($data[5]);
                $this->ende = $this->ende . $this->makeTime($data[5]);
            } else {
                //ganztägig
                
                $this->start = $this->start;//."T000000";
                $this->ende = $this->ende;//."T235959";
            }
        }
        
        return $this;
    }
    
    /**
     *Konstruktor nach Übergabe von Datensatz aus DB
     *
     * @param $d Array
     */
    public function createFromDB($d) {
        //var_dump($d);
        $this->typ = $d[0];
        $this->start = $d[1];
        $this->ende = $d[2];
        $this->staff = $d[3];
        $mt = $this->start[4] . $this->start[5];
        $this->jahr = $this->start[0] . $this->start[1] . $this->start[2] . $this->start[3];
        $this->findMonth($mt);
        
        if (strlen($this->start) < 9) {
            //ganztägiger Termin
            if ($this->start == $this->ende) {
                //eintägiger Termin
                $this->sTimeStamp = $this->start;
                $this->sday = $this->reverseDate($this->start);
                $this->sweekday = $this->getWeekday($this->start);
                
            } else {
                //mehrtägig
                $this->sTimeStamp = $this->start;
                $this->sday = $this->reverseDate($this->start);
                $this->eday = $this->reverseDate($this->ende);
                $this->sweekday = $this->getWeekday($this->start);
                $this->eweekday = $this->getWeekday($this->ende);
            }
        } else {
            //Start und Endzeiten angegeben
            if ($this->start == $this->ende) {
                //nur Startzeitangegeben
                $arr = explode("T", $this->start);
                $tag = $arr[0];
                $zeit = $arr[1];
                $this->sTimeStamp = $tag;
                $this->sday = $this->reverseDate($tag);
                $this->stime = $this->reverseTime($zeit);
                $this->sweekday = $this->getWeekday($tag);
            } else {
                //Start und Endzeit angegeben
                $arr = explode("T", $this->start);
                $stag = $arr[0];
                $szeit = $arr[1];
                $this->sTimeStamp = $stag;
                $arr = explode("T", $this->ende);
                $etag = $arr[0];
                $ezeit = $arr[1];
                if ($stag == $etag) {
                    //eintägiger Termin
                    $this->sday = $this->reverseDate($stag);
                    $this->stime = $this->reverseTime($szeit) . "-" . $this->reverseTime($ezeit);
                    $this->sweekday = $this->getWeekday($stag);
                } else {
                    //mehrtägiger Termin
                    $this->sday = $this->reverseDate($stag);
                    $this->stime = $this->reverseTime($szeit);
                    $this->eday = $this->reverseDate($etag);
                    $this->etime = $this->reverseTime($ezeit);
                    $this->sweekday = $this->getWeekday($stag);
                    $this->eweekday = $this->getWeekday($etag);
                }
            }
        }
        
        return $this;
    }
    
    
    /*
    *Datumsformat anpassen
    *@param $datum String
    *@return String
    */
    private function makeDate($datum) {
        $dA = explode(".", $datum);
        $datum = $dA[2] . $dA[1] . $dA[0];
        
        return $datum;
    }
    
    /**
     *Datumsformat in DD.MM.YYY Format anpassen
     *
     * @param string Datum im YYYYMMDD Format
     *
     * @return String
     */
    private function reverseDate($datum) {
        return $datum[6] . $datum[7] . "." . $datum[4] . $datum[5] . "." . $datum[0] . $datum[1] . $datum[2] . $datum[3];
    }
    
    
    
    /**
     *zeitformat anpassen
     *
     * @param $time string
     *
     * @return String
     */
    private function makeTime($time) {
        $zeit = "T000000";
        if (strlen($time) == 5) {
            $tA = explode(":", $time);
            $zeit = "T" . $tA[0] . $tA[1] . "00";
        }
        
        return $zeit;
    }
    
    /**
     *Zeitformat in hh:mm
     *
     * @param string Zeit im hhmmss Format
     *
     * @return String
     */
    private function reverseTime($time) {
        return $time[0] . $time[1] . ":" . $time[2] . $time[3];
    }
    
    
    /**
     *ermittelt den Wochentag anhand eines Datumsformat
     *
     * @param String Datum im Format YYYMMDD
     *
     * @return String
     */
    private function getWeekday($datum) {
        $wochentage = array('So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa');
        $monat = $datum[4] . $datum[5];
        $tag = $datum[6] . $datum[7];
        $jahr = $datum[0] . $datum[1] . $datum[2] . $datum[3];
        $datum = getdate(mktime(0, 0, 0, $monat, $tag, $jahr));
        $wochentag = $datum['wday'];
        
        return $wochentage[$wochentag];
    }
    
    
    
    /**
     *Ermittelt den Monat in Textform
     *
     * @param $mt String Monat
     */
    private function findMonth($mt) {
        $this->monatNum = $mt;
        switch ($mt) {
            case "01":
                $month = "Januar";
                break;
            case "02":
                $month = "Februar";
                break;
            case "03":
                $month = "März";
                break;
            case "04":
                $month = "April";
                break;
            case "05":
                $month = "Mai";
                break;
            case "06":
                $month = "Juni";
                break;
            case "07":
                $month = "Juli";
                break;
            case "08":
                $month = "August";
                break;
            case "09":
                $month = "September";
                break;
            case "10":
                $month = "Oktober";
                break;
            case "11":
                $month = "November";
                break;
            case "12":
                $month = "Dezember";
                break;
        }
        $this->monat = $month;
    }
    
}

?>