<?php

    /**
     * User class used to get user related data easily
     */
    class User extends Printable
    {

        /**
         * @var int 0 -> Admin, 1 -> Parent/Guardian, 2 -> Teacher
         */
        protected $type;
        /**
         * @var int userId
         */
        protected $id;
        /**
         * @var user's email
         */
        protected $email;
        /**
         * @var $surname string Surname name of the user
         */
        protected $surname;
        /**
         * @var $surname string Name name of the user
         */
        protected $name;

        /**
         *Construct method of User class
         *
         * @param int $id userId
         */
        public function __construct($id, $type, $email, $name = null, $surname = null)
        {
            $this->id = $id;
            $this->type = $type;
            $this->email = $email;
            $this->name = $name;
            $this->surname = $surname;
        }

        /**
         *Returns user ID
         *
         * @return int id
         */
        public function getId()
        {
            return $this->id;
        }

        /**
         *Returns user type (0 for admin, 1 for parent, 2 for teacher)
         *
         * @return int type
         */
        public function getType()
        {
            return $this->type;
        }

        /**
         * @return string
         */
        public function getFullname()
        {
            return $this->name . ' ' . $this->surname;
        }

        /**
         * @return User
         */
        public function getEmail()
        {
            return $this->email;
        }

        /**
         * @return array[String => Data] used for creating __toString and jsonSerialize
         */
        public function getData()
        {
            return array("userid" => $this->id, "type" => $this->type, "name" => $this->name, "surname" => $this->surname, "email" => $this->email);
        }

        /** Returns class type
         *
         * @return string
         */
        public function getClassType()
        {
            return "Student";
        }
    }


    /**
     * Guardian class as subclass of User class representing parents
     */
    class Guardian extends User
    {

        /**
         * @var array
         */
        private $children;
        /**
         * @var int
         */
        private $parentId;

        /**
         * Contructor of Parent class
         *
         * @param int $id userId
         * @param string $email
         */
        public function __construct($id, $email, $parentId)
        {
            parent::__construct($id, 1, $email);

            $this->parentId = $parentId;
            $this->children = Model::getInstance()->getChildrenByParentUserId($this->id);

        }

        /**
         *Returns child(ren)'s id(s)
         *
         * @return array[Student] children
         */
        public function getChildren()
        {
            return $this->children;
        }

        /**
         * Returns all classes that are related to the parent's children
         *
         * @return array[String]
         */
        public function getClasses()
        {
            if ($this->getChildren() == null)
                return array();

            $model = Model::getInstance();
            $classes = array();
            foreach ($this->getChildren() as $student/** @var $student Student */)
            {
                $classes[] = $student->getClass();
            }

            return $classes;
        }

        /**
         * Returns all teachers that teach any of the parents children
         *
         * @return array[Teacher] teachers
         */
        public function getTeachers()
        {

            if ($this->getChildren() == null)
                return array();

            $model = Model::getInstance();
            $classes = $this->getClasses();

            $teachers = array();
            foreach ($classes as $class)
            {
                $teacher = $model->getTeachersByClass($class);
                if ($teacher == null)
                    continue;
                $teachers = array_merge($teachers, $teacher);
            }

            sort($teachers);

            return $teachers;
        }

        /**
         * Get all teachers of all children
         *
         * @return array(Teacher,Child)
         */
        public function getTeachersOfAllChildren()
        {
            $children = $this->getChildren();
            $myArr = array();
            /** @var Student $child */
            foreach ($children as $child)
            {
                /** @var Teacher $teacher */
                foreach ($child->getTeachers() as $teacher)
                {
                    if (!isset($myArr[$teacher->getId()]))
                        $myArr[$teacher->getId()] = array("teacher" => $teacher, "students" => array());
                    array_push($myArr[$teacher->getId()]["students"], $child);
                }
            }

            return $myArr;
        }

        /**
         * @return int parent ID
         */
        public function getParentId()
        {
            return $this->parentId;
        }

        /**
         * @return array[String => mixed] returns all data of this class as array
         */
        public function getData()
        {
            return array_merge(parent::getData(), array("parentId" => $this->parentId, "children" => $this->children));
        }

        /** Returns class type
         *
         * @return string
         */
        public function getClassType()
        {
            return "Guardian";
        }

        /**
         *returns booked timeSlots
         *
         * @return array(Timestamp anfang)
         */
        public function getAppointments()
        {
            $model = Model::getInstance();
		$appointments = array();
            $appointmentData =  $model->getAppointmentsOfParent($this->parentId);
		foreach ($appointmentData as $a) {
		$appointments[] = $a['slotId'];
		}
		return $appointments;
        }

	/**
         *returns TeacherIds of booked timeSlots
         *
         * @return array(Timestamp anfang)
         */
        public function getAppointmentTeachers()
        {
            $model = Model::getInstance();
		$appointments = array();
            $appointmentData =  $model->getAppointmentsOfParent($this->parentId);
		foreach ($appointmentData as $a) {
		$appointments[] = array("teacherId"=>$a['teacherId']);
		}
		return $appointments;
        }


	/**
	*finds bookedTeacher Ids
	*
	*@return array(int)
	*/
	public function getBookedTeachers(){
		$bookedTeachers = array();
		$appointments = $this->getAppointmentTeachers();
		foreach ($appointments as $appointment) {
			$teachers[] = $appointment['teacherId'];
		}
       return $teachers;
	}

	

	

    }


    /**
     *Teacher class as subclass of User class
     */
    class Teacher extends User
    {


        /**
         * @var int lessonAmount (Deputat)
         */
        protected $lessonAmount;

        /**
         * @var string $ldapName
         */
        protected $ldapName;
		
				
        /**
         * Contructor of Teacher class
         *
         * @param int $id userId
         * @param string $email
         */
        public function __construct($email, $teacherId, $rawData = null)
        {

            $nameData = Model::getInstance()->getTeacherNameByTeacherId($teacherId, $rawData);

            parent::__construct($teacherId, 2, $email, $nameData['name'], $nameData['surname']);

            $this->ldapName = Model::getInstance()->getTeacherLdapNameByTeacherId($teacherId, $rawData);
            $this->lessonAmount = Model::getInstance()->getTeacherLessonAmountByTeacherId($teacherId, $rawData);
        }

        /**
         * @return array[String => mixed] returns all data of this class as array
         */
        public function getData()
        {
            $parentData = parent::getData();

            return array_merge($parentData, array("lessonAmount" => $this->lessonAmount, "ldapName" => $this->ldapName));
        }

        /** Returns class type
         *
         * @return string
         */
        public function getClassType()
        {
            return "Teacher";
        }

        /**
         *Returns lesson amount of teacher (Deputat)
         *
         * @return int
         */
        public function getLessonAmount()
        {
            return $this->lessonAmount;
        }

        /**
         *Returns required slots according to lessonAmount
         *
         * @return int
         */
        public function getRequiredSlots()
        {
            $HALFAMOUNT = 13.5;
			$MINAMOUNT = 12.5;
			$FULL = 10;
			$HALF = 5;
			$REDUCTION = 4;
			$amount = $FULL;
			$lessons = $this->getLessonAmount();
			if ($lessons < $HALFAMOUNT) { $amount = $FULL - $REDUCTION;}
			if ($lessons < $MINAMOUNT) { $amount = $HALF;}	
            return $amount;
        }

        /**
         *returns missing slots for openday
         *
         * @return int
         */
        public function getMissingSlots()
        {
            $required = $this->getRequiredSlots();
            $model = Model::getInstance();

            $doneyet = count($this->getAssignedSlots());

            return $required - $doneyet;
        }

        /**
         *creates and returns an array with all slots included the ones assigned by Teacher
         *
         * @return array(int,string,string,bool)
         */
        public function getSlotListToAssign()
        {
            $slotList = array();
            $model = Model::getInstance();
            $assignedSlots = $this->getAssignedSlots();
            $allSlots = $model->getSlots();
            foreach ($allSlots as $slot)
            {
                foreach ($assignedSlots as $aSlot)
                {
                    if ($slot['id'] == $aSlot)
                    {
                        //this slot is assigned by Teacher
                        $slot['assigned'] = true;
                    }

                }
                $slotList[] = $slot;
            }

            return $slotList;
        }

        /**
         *Enters a teacher slot into DB
         *
         * @param int slotId
         */
        public function setAssignedSlot($slotId)
        {
            $model = Model::getInstance();
            $model->setAssignedSlot($slotId, $this->id);
        }

        /**
         *returns AssignedSlots
         *
         * @return array(int)
         */
        public function getAssignedSlots()
        {
            $model = Model::getInstance();
            $assignedSlots = $model->getAssignedSlots($this->getId());

            return $assignedSlots;
        }

        /**
         *returns bookable Slots and booked Slots by a parent as array
         *
         * @return array(int bookingId, Timestamp anfang, Timestamp ende, int parentId)
         */
        public function getAllBookableSlots($parentId)
        {
            $model = Model::getInstance();

            return $model->getAllBookableSlotsForParent($this->id, $parentId);
        }
    }

    class Admin extends User
    {

        function __construct($id, $email)
        {
            parent::__construct($id, 0, $email);
        }

        /** Returns class type
         *
         * @return string
         */
        public function getClassType()
        {
            return "Admin";
        }

    }

    /**
     * Class Student
     */
    class Student extends Printable
    {

        /**
         * @var int student ID
         */
        protected $id;
        /**
         * @var string student's class
         */
        protected $class;
        /**
         * @var string student's surname
         */
        protected $surname;
        /**
         * @var string student's name
         */
        protected $name;
        /**
         * @var int parent ID
         */
        protected $eid;
        /**
         * @var string student's birthday
         */
        protected $bday;

        public function __construct($id, $class, $surname, $name, $bday, $eid = null)
        {
            $this->id = $id;
            $this->class = $class;
            $this->surname = $surname;
            $this->name = $name;
            $this->bday = $bday;
            $this->eid = $eid;
        }


        /**
         * Returns student id
         *
         * @return int
         */
        public function getId()
        {
            return $this->id;
        }

        /**
         * @return string
         */
        public function getClass()
        {
            return $this->class;
        }

        /**
         * @return string
         */
        public function getSurname()
        {
            return $this->surname;
        }

        /**
         * @return string
         */
        public function getName()
        {
            return $this->name;
        }

        /**
         * @return int
         */
        public function getEid()
        {
            return $this->eid;
        }

        public function getFullName()
        {
            return $this->getName() . " " . $this->getSurname();
        }

        /**
         * @return string
         */
        public function getBday()
        {
            return $this->bday;
        }

        /**
         * @return array[String => Data] used for creating __toString and jsonSerialize
         */
        public function getData()
        {
            return array("id" => $this->id, "class" => $this->class, "surname" => $this->surname, "name" => $this->name, "eid" => $this->eid, "bday" => $this->bday);
        }

        /** Returns class type
         *
         * @return string
         */
        public function getClassType()
        {
            return "Student";
        }

        /**
         * Get all teachers teaching this student
         *
         * @return array(Teachers)
         */
        public function getTeachers()
        {
            $model = Model::getInstance();

            $teachers = $model->getTeachersByClass($this->getClass());
            if ($teachers == null)
                return array();

            sort($teachers);

            return $teachers;
        }


    }


?>
