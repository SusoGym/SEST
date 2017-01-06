<?php namespace administrator;

    /**
     * The model class
     */
    class Model extends \Model
    {

        /**
         * @var Model
         */
        protected static $model;

        /**
         *Konstruktor
         */
        protected function __construct()
        {
            parent::__construct();

        }

        static function getInstance()
        {
            return (self::$model == null || !(self::$model instanceof Model)) ? self::$model = new Model() : self::$model;
        }

        /**
         *read datafields from database
         *
         * @param bool $student
         * @return array datafield names
         */
        public function readDBFields($student)
        {
            ($student) ? $table = "schueler" : $table = "lehrer";
            $data = self::$connection->selectFieldNames("SELECT * FROM " . $table);

            return $data;

        }

        /**
         *check if data exist in database
         *
         * @param int $id
         * @param bool $student
         * @return bool existence
         */
        public function checkDBData($student, $id)
        {
            ($student) ? $table = "schueler" : $table = "lehrer";
            $data = self::$connection->selectValues("SELECT id FROM $table where id=$id");
            if (count($data) > 0)
            {
                return true;
            } else
            {
                return false;
            }
        }


        /**
         *update data
         *
         * @param bool
         * @param int
         * @param array
         */
        public function updateData($student, $id, $line)
        {
            ($student) ? $table = "schueler" : $table = "lehrer";
            $string = null;
            foreach ($line as $key => $value)
            {
                $key = trim($key);
                $value = trim($value);
                $value = addslashes($value);
                if (isset($string))
                {
                    $string = $string . ",$key=\"$value\" ";
                } else
                {
                    $string = "$key=\"$value\" ";
                }
            }
            $string = $string . ",upd=1 WHERE id=$id";
            $string = "UPDATE $table SET " . $string;

            //echo $string.'<br>';
            self::$connection->straightQuery($string);
        }

        /**
         *insert data
         *
         * @param bool
         * @param array
         */
        public function insertData($student, $line)
        {
            ($student) ? $table = "schueler" : $table = "lehrer";
            $fieldstring = null;
            $valuestring = null;
            foreach ($line as $key => $value)
            {
                $key = trim($key);
                $value = trim($value);
                $value = addslashes($value);
                if (isset($fieldstring))
                {
                    $fieldstring = $fieldstring . ",`$key`";
                } else
                {
                    $fieldstring = "`$key`";
                }
                if (isset($valuestring))
                {
                    $valuestring = $valuestring . ",'$value'";
                } else
                {
                    $valuestring = "'$value'";
                }
            }
            $fieldstring = $fieldstring . ",`upd`";
            $valuestring = $valuestring . ",'1'";
            $string = "INSERT INTO $table (" . $fieldstring . ") VALUES (" . $valuestring . ")";
            //echo $string.'<br>';
            self::$connection->insertValues($string);

        }

        /**
         * delete unused data from DB
         *
         * @param bool $student
         * @return int amount of deletions
         */
        public function deleteDataFromDB($student)
        {
            $toDelete = 0;
            ($student) ? $table = "schueler" : $table = "lehrer";
            $data = self::$connection->selectValues("SELECT id FROM $table WHERE upd=0");
            $toDelete = count($data);
            self::$connection->straightQuery("DELETE FROM $table WHERE upd=0");

            return $toDelete;
        }

        /**
         *set update status to zero
         *
         * @param bool $student
         */
        public function setUpdateStatusZero($student)
        {
            ($student) ? $table = "schueler" : $table = "lehrer";
            self::$connection->straightQuery("UPDATE $table SET upd=0");
        }



        /**
         *
         * @return array(string) with form names
         */
        public function getForms()
        {
            $data = self::$connection->selectValues("SELECT DISTINCT klasse FROM schueler ORDER BY klasse"); // returns data[n][data]

            $forms = array();

            foreach ($data as $item)
            {
                array_push($forms, $item[0]);
            }

            return $forms;

        }


        /**
         * connect teacher with form
         *
         * @param array(int) with teacher Ids
         * @param string $form class
         */
        public function setTeacherToForm($teacher, $form)
        {

            $data = self::$connection->selectAssociativeValues("SELECT * FROM unterricht WHERE klasse='$form'");

            $dbIds = array();

            $new = array();
            $same = array();
            $deleted = array();

            if ($teacher == null)
            { // delete all teachers

                self::$connection->straightQuery("DELETE FROM unterricht WHERE klasse='$form'");

                return;
            } else
                if ($data != null)
                {
                    foreach ($data as $value)
                    {
                        $id = $value['lid'];
                        $dbIds[] = $id;
                    }

                    foreach ($teacher as $id)
                    {
                        if (in_array($id, $dbIds))
                            $same[] = $id;
                        else
                            $new[] = $id;
                    }

                    foreach ($dbIds as $id)
                    {
                        if (!in_array($id, array_merge($new, $same)))
                            $deleted[] = $id;
                    }
                } else
                {
                    $new = $teacher;
                }

            $query = "";

            foreach ($deleted as $dId)
                $query .= "DELETE FROM unterricht WHERE klasse='$form' AND lid=$dId; ";
            foreach ($new as $aId)
                $query .= "INSERT INTO unterricht(klasse, lid) VALUES('$form', $aId); ";

            if ($query != '')
                self::$connection->straightMultiQuery($query);

        }

        /**
         * get teachers in form
         *
         * @param string $form
         * @return array[teacherId](array(form) )
         */
        public function getTeachersOfForm($form)
        {
            $teachersOfForm = array();
            $teachers = array();
            $tchrs = self::$connection->selectValues("SELECT lid FROM unterricht WHERE klasse=\"$form\" ");
            if (count($tchrs) > 0)
            {
                foreach ($tchrs as $t)
                {
                    $teachers[] = $t[0];
                }
            } else
            {
                $teachers = null;
            }

            $teachersOfForm[$form] = $teachers;
            unset($teachers);

            return $teachersOfForm;
        }


        /**
         *delete Slot from DB
         *
         * @param int slotId
         */
        public function deleteSlot($id)
        {
            self::$connection->straightQuery("DELETE FROM time_slot WHERE id=$id");
        }

        /**
         *insert Slot into DB
         *
         * @param string $start
         * @param string $end
         */
        public function insertSlot($start, $end)
        {
            $start = $this->makeDateTime($start);
            $end = $this->makeDateTime($end);

            return self::$connection->insertValues("INSERT INTO time_slot (`id`,`anfang`,`ende`) VALUES ('','$start','$end') ");
        }

	  
	/**
	* reset all bookable slots
	*/
	public function clearBookableSlots(){
		self::$connection->straightQuery("TRUNCATE TABLE bookable_slot");
		}		


        /**
         *create bookable appointments according to lessonVolume of teacher
         *hardCoded - to be adapted to option Data
         *
         * @param int slotId
         */
        public function createBookableSlots($slotId)
        {
            $FULL = 13;
            $data = self::$connection->selectValues("SELECT id FROM lehrer WHERE deputat>$FULL");
            foreach ($data as $d)
            {
                self::$connection->straightQuery("INSERT INTO bookable_slot (`id`,`slotid`,`lid`,`eid`) VALUES ('','$slotId','$d[0]',NULL)");
            }
        }

        /**
         *create DateTime Format
         *
         * @param string
         * @return DateTime
         */
        private function makeDateTime($string)
        {
            $da = explode(" ", $string);
            $date = $da[0];
            $time = $da[1];
            $dateArr = explode(".", $date);
            $newDate = $dateArr[2] . "-" . $dateArr[1] . "-" . $dateArr[0] . " " . $time;

            return $newDate;
        }


        /**
         *returns options for admin purpose
         *
         * @return array(string)
         */
        public function getOptionsForAdmin()
        {
            $options = array();
            $data = self::$connection->selectValues("SELECT kommentar, type, value, field FROM options ORDER BY ordinal");
            if (isset($data))
            {
                foreach ($data as $d)
                {
                    $options[] = array("kommentar" => $d[0], "type" => $d[1], "value" => $d[2], "field" => $d[3]);
                }
            }

            return $options;
        }

        /**
         *updates options after changes
         *
         * @param array POST
         */
        public function updateOptions($data)
        {
            foreach ($data as $key => $value)
            {
                self::$connection->straightQuery("UPDATE options SET value =\"$value\" WHERE type=\"$key\" ");
            }
        }

	/**
	*Eintrag aller Termine in die Datenbank
	*@param $termine Array(Terminobjekt)
	*/
	public function addEventsToDB($termine){
	//Tabelle leeren
	self::$connection->straightQuery("TRUNCATE termine");
	foreach($termine as $t){
			$query="INSERT INTO termine (`tNr`,`typ`,`start`,`ende`,`staff`) VALUES ('','$t->typ','$t->start','$t->ende','$t->staff')	";
			self::$connection->insertValues($query);
			}
		}

	/**
	* get values from ini-file
	* @return string
	*/
	public function getIniParams(){
		return self::$connection->getIniParams();
		}	
    }


?>
