<?php namespace administrator;

/**
 *Class FileHandler
 */
class FileHandler
{

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
    public function __construct($file)
    {
        $this->file = $file;
        $this->model = Model::getInstance();
    }


    /**
     *read headerline
     *
     * @return array(string) name of datafields in file
     */
    public function readHead()
    {
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
    public function readDBFields($student)
    {
        return $this->model->readDBFields($student);
    }


    /**
     *read sourceData daten aus Datei lesen
     *
     * @return array zeile
     */
    private function readSourceData()
    {
        $fh = fopen($this->file, "r");
        $line = trim(fgets($fh, "1024"));
        $sourceField = explode(";", $line);
        $sourceData = array();
        while (!feof($fh))
        {
            $sourceData[] = fgets($fh, "1024");
        }
        fclose($fh);

        return $sourceData;
    }


	*Quelldatei mit Termindaten auslesen
	fclose($fh);
	return $events;
	}	

    /**
     *updateData aktualisiert Datenbank auf Basis einer csv datei
     *
     * @param array $data Zuordnung Quell zu Zielfeld
     * @param array $data
     * @return array amount inserted and deleted datasets
     */
    public function updateData($student, $data)
    {
        $insertCounter = 0;
        $updateCounter = 0;
        $changesApplied = array();
        $this->model->setUpdateStatusZero($student);
        $sourceLines = $this->readSourceData();
        $lineFieldValue = array();
        $x = 0;
        foreach ($sourceLines as $line)
        {
            $lineArr = explode(";", $line);
            $y = 0;
            foreach ($data as $d)
            {
                if (strlen($line) > 3)
                {
                    $lineFieldValue[$x][$d['target']] = $lineArr[$y];
                    $y++;
                }
            }
            $x++;
        }

        foreach ($lineFieldValue as $l)
        {
            if ($this->model->checkDBData($student, $l["id"]))
            {
                $this->model->updateData($student, $l["id"], $l);
                $updateCounter++;
            } else
            {
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
    public function deleteDataFromDB($student)
    {
        return $this->model->deleteDataFromDB($student);

    }

}

?>