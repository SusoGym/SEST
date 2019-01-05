<?php namespace administrator;

/**
 *Class FileHandler
 */
class FileHandler {
    
    /**
     * @var string path to file
     */
    private $file;
    
    
    /**
     *var model Object
     */
    private $model;
    
    /**
     *Constructor
     *
     * @param string $file path to file
     */
    public function __construct($file) {
        $this->file = $file;
        $this->model = Model::getInstance();
    }
    
    
    /**
     *read headerline
     *
     * @return array(string) name of datafields in file
     */
    public function readHead() {
        $fh = fopen($this->file, "r");
        $line = trim(fgets($fh, "1024"));
        $sourceField = explode(";", $line);
        fclose($fh);
        
        return $sourceField;
    }
    
    
    /**
     *read DB Datafields
     *
     * @param bool $student
     * @return array(string) name of datafields in database
     */
    public function readDBFields($student) {
        return $this->model->readDBFields($student);
    }
    
    
    /**
     *read sourceData daten aus Datei lesen
     *
     * @return array zeile
     */
    private function readSourceData() {
        $fh = fopen($this->file, "r");
        $line = trim(fgets($fh, "1024"));
        $sourceField = explode(";", $line);
        $sourceData = array();
        while (!feof($fh)) {
            $sourceData[] = fgets($fh, "1024");
        }
        fclose($fh);
        
        return $sourceData;
    }
    
    
	/**
	*Quelldatei mit Termindaten auslesen
	* @return array(Termine)
	*/
    public function readEventSourceFile() {
        $events = array();
        $fh = fopen($this->file, "r");
        $x = 0;
        while (!feof($fh)) {
            $line = trim(fgets($fh));
            $lineArr = explode(";", $line);
            $event = new \Termin();
            $events[$x] = $event->createFromCSV($lineArr);
            $x++;
        }
        fclose($fh);
        
        return $events;
    }
	
	/**
	* read sourc efile with lessons
	* @return array()
	*/
    public function readLessonsSourceFile() {
        $lessons = array();
        $fh = fopen($this->file, "r");
        $x = 0;
        while (!feof($fh)) {
            $line = trim(fgets($fh));
            $lineArr = explode(";", $line);
            $lessons[$x] = array("class"=>$lineArr[0],"subject"=>$lineArr[1],"teacher"=>$lineArr[2]);
            $x++;
        }
        fclose($fh);
        return $lessons;
    }
    
    /**
     *updateData aktualisiert Datenbank auf Basis einer csv datei
     *
     * @param array $data Zuordnung Quell zu Zielfeld
     * @param array $data
     * @return array amount inserted and deleted datasets
     */
    public function updateData($student, $data) {
        $insertCounter = 0;
        $updateCounter = 0;
        $changesApplied = array();
        $this->model->setUpdateStatusZero($student);
        $sourceLines = $this->readSourceData();
        $lineFieldValue = array();
        $x = 0;
        foreach ($sourceLines as $line) {
            $lineArr = explode(";", $line);
            $y = 0;
            foreach ($data as $d) {
                if (strlen($line) > 3) {
                    $lineFieldValue[$x][$d['target']] = $lineArr[$y];
                    $y++;
                }
            }
            $x++;
        }
        
        foreach ($lineFieldValue as $l) {
            if ($this->model->checkDBData($student, $l["ASV_ID"])) {
                $this->model->updateData($student, $l["ASV_ID"], $l);
                $updateCounter++;
            } else {
                $this->model->insertData($student, $l);
                $insertCounter++;
            }
        }
        $changesApplied[0] = $updateCounter;
        $changesApplied[1] = $insertCounter;
        
        return $changesApplied;
    }
    
    
    /**
     *delete unused data from DB
     *
     * @param bool
     * @return int amount of deletions
     */
    public function deleteDataFromDB($student) {
        return $this->model->deleteDataFromDB($student);
        
    }
    
    /**
     * create csv-file
     *
     * @param string filename
     * @param array (array(string)) data
     */
    public function createCSV($data) {
        $f = fopen($this->file, "w");
        foreach ($data as $line) {
            fwrite($f, $line);
        }
        fclose($f);
    }
	
	/**
     * create html-file
     *
     * @param string filename
     * @param array (array(string)) data
	 * @param int maxPeriod (in workdays)
     */
    public function createHTML($data,$maxPeriod) {
		//Define two arrays used in the process
		$absenceDays = array(); //array including all days of the time period to show
		for ($x = $maxPeriod; $x >= 0; $x--) {
			$wd = $this->getGermanWeekday(date("w", strtotime('-'.$x.' weekdays') ));
			array_push($absenceDays,array(	"calcdate"=>date("Y-m-d", strtotime('-'.$x.' weekdays') ),
											"showdate"=>$wd.' '.date("d.m.Y", strtotime('-'.$x.' weekdays') ) 
											)
						);	
			}
		$absenteeForms = $data['formsWithAbsences']; //Array including all forms with absent pupils
		$f = fopen($this->file, "w");

		
		foreach ($absenceDays as $day) {
		//filter Results  by individual Days and Forms
		$dayData = array_filter($data['absentPupils'],$this->filter_datum($day['calcdate']) );
			
			//Write Day as header
			$line = '<p style="font-size: 19px;font-weight:bold">'.$day['showdate'].'</p>';
			$line .= "\r\n";
			fwrite($f, $line); 
			$colCount = 1;
			$line = "<table>\r\n<tbody>\r\n";
			fwrite($f, $line);
			foreach($absenteeForms as $form) {
				
				//Filter by forms with absent students
				$dayDataForms = array_filter($dayData,$this->filter_klasse($form) );
				if (!empty($dayDataForms) ) {
					
					if ($colCount == 1) {
					$line = "<tr>\r\n";
					fwrite($f, $line);
					}
					//Write form as subheader
					$line = '<td><span style="font-size: 16px;font-weight:bold">'.$form.'</span><br>';
					$line .= "\r\n";
					fwrite($f, $line);
					foreach ($dayDataForms as $dataset) {
						//Write Students
						$line = '<span style="font-size:12px">'.$dataset['name']; //.' ('.$dataset['klasse'].') ';	
						if($dataset['beurlaubt'] == 1) 
							$line .= '<b style="font-size:8px">&nbsp;[beurl.]</b>';
						$line .= "</span><br/>\r\n";
						fwrite($f,$line);
						
						}
				$line = "</td>\r\n";
				fwrite($f, $line);
				if ($colCount == 3) {
					$line = "</tr>\r\n";
					fwrite($f, $line);
					$colCount = 1;
					} else {
					$colCount ++;
					}					
				}
			}
		if  ($colCount != 1) {
				$line = "</tr>\r\n";
				fwrite($f, $line);	
				}
		fwrite($f,"</tbody></table>\r\n<hr>\r\n");	
		}
		fclose($f);
			
    }
	
	/**
	* callback for PHP array_filter function used in this class
	*/
	private function filter_klasse($value) {				
		return function ($v) use ($value) {
			return $v['klasse'] == $value;
			};
		}	
	
	/**
	* callback for PHP array_filter function used in this class
	*/
	private function filter_datum($value) {				
		return function ($v) use ($value) {
			return $v['ende'] >= $value && $v['beginn']<= $value;
			};
		}	
	/**
	* return german Weekday
	* @param int
	* @return string
	*/
	private function getGermanWeekday($numericWeekday) {
	$wd = null;
	switch ($numericWeekday) {
			case 1:
				$wd = "Montag, ";
				break;
			case 2:
				$wd = "Dienstag, ";
				break;
			case 3:
				$wd = "Mittwoch, ";
				break;
			case 4:
				$wd = "Donnerstag, ";
				break;
			case 5:
				$wd = "Freitag, ";
				break;
			};
	return $wd;
	}	
    
}

?>