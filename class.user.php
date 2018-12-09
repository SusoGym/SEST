<?php

use \base\Printable;

/**
 * User class used to get user related data easily
 */
class User extends Printable {
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
     * @var bool $receiveNewsMail
     */
    protected $receiveNewsMail;
    /**
     * @var bool $HTMLNews
     */
    protected $HTMLNews;
	
	/**
	* @var string $dsgvo
	*/
	protected $dsgvo = null;
    
    
    /**
     *Construct method of User class
     *
     * @param int    $id userId
     * @param int    $type
     * @param string $email
     * @param string $name
     * @param string $surname
     */
    public function __construct($id, $type, $email, $name = null, $surname = null) {
        $this->id = intval($id);
        $this->type = intval($type);
        $this->email = $email;
        $this->name = $name;
        $this->surname = $surname;
		
    }
    
    /**
     *Returns user ID
     *
     * @return int id
     */
    public function getId() {
        return $this->id;
    }
    
    /**
     *Returns user type (0 for admin, 1 for parent, 2 for teacher, 3 for StudentUser)
     *
     * @return int type
     */
    public function getType() {
        return $this->type;
    }
    
    /**
     * @return string
     */
    public function getFullname() {
        return $this->name . ' ' . $this->surname;
    }
    
    public function getName() {
        return $this->name;
    }
    
    public function getSurname() {
        return $this->surname;
    }
    
    /**
     * @return User
     */
    public function getEmail() {
        return $this->email;
    }
    
    /**
     * return ReceiveNewsletterMail
     *
     * @return bool
     */
    public function getReceiveNewsMail() {
        return $this->receiveNewsMail;
    }
	
	/*
	*
	*/
	public function acceptDsgvo(){
		Model::getInstance()->acceptDsgvo($this);
	}
    
    /**
     * get NewsMail HTML status
     *
     * @return bool
     */
    public function getNewsStatus() {
        return $this->HTMLNews;
    }
	
	/**
	* get dsgvo status
	* @return string
	*/
	public function getDsgvo(){
		return $this->dsgvo;
	}
    
    /**
     * set receive News Mail Status
     *
     * @param bool $status
     */
    public function setReceiveNewsMail($status) {
        $this->receiveNewsMail = $status;
    }
    
    /**
     * set receive News Mail Status
     *
     * @param bool $status
     */
    public function setHTMLNews($status) {
        $this->HTMLNews = $status;
    }
    
    /**
     * returns newsletter Email reception
     *
     * @return int (1 = HTML / 0 = PlainText)
     */
    /*public function getNewsletterReceptionType(){
        $teacher = ($this->getType()=="Teacher") ?	 true : false ;
        $model = Model::getInstance();
        return $model->getNewsletterReceptionType($teacher); //corresponding function in model still missing
        }
    */
    
    /**
     * returns status of mail service for newsletters
     *
     * @return bool
     */
    public function getNewsMailStatus() {
        $teacher = ($this->getType() == 2) ? true : false;
        
        return Model::getInstance()->getNewsMailStatus($this->id, $teacher);
    }
    
    /**
     * returns format of newsletter mail true if HTML
     *
     * @return bool
     */
    public function getNewsHTMLStatus() {
        $teacher = ($this->getType() == 2) ? true : false;
        
        return Model::getInstance()->getNewsHTMLStatus($this->id, $teacher);
    }
    
    
    /**
     * @return array[String => Data] used for creating __toString and jsonSerialize
     */
    public function getData() {
        return array("id" => $this->id, "type" => $this->type, "name" => $this->name, "surname" => $this->surname, "email" => $this->email);
    }
    
    /** Returns class type
     *
     * @return string
     */
    public function getClassType() {
        return "User";
    }
}

/**
 * Guardian class as subclass of User class representing parents
 */
class Guardian extends User {
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
     * @param int    $id userId
     * @param string $email
     * @param        int parentId
     */
    public function __construct($id, $email, $parentId, $surname = null, $name = null) {
        parent::__construct($id, 1, $email, $name, $surname);
        $this->parentId = $parentId;
        $this->children = Model::getInstance()->getChildrenByParentUserId($this->id);
		$this->dsgvo = Model::getInstance()->getDsgvoStatus($this);
    }
    
    /**
     *Returns child(ren)'s id(s)
     *
     * @return array[Student] children
     */
    public function getChildren() {
        return $this->children;
    }
    
    /**
     *Returns child(ren)'s id(s) of EST relevant children only
     *
     * @param limit int (most senior year group for EST plus one)
     *
     * @return array[Student] children
     */
    public function getESTChildren($limit) {
        $arr = array();
        
        /** @var Student $child */
        foreach ($this->children as $child) {
            if ($child->getClass() < $limit)
                array_push($arr, $child);
        }
        
        return $arr;
    }
    
    /**
     * Returns all classes that are related to the parent's children
     *
     * @return array[String]
     */
    public function getClasses() {
        if ($this->getChildren() == null)
            return array();
        $model = Model::getInstance();
        $classes = array();
        foreach ($this->getChildren() as $student/** @var $student Student */) {
            $classes[] = $student->getClass();
        }
        
        return $classes;
    }
    
    /**
     * Returns all teachers that teach any of the parents children
     *
     * @return array[Teacher] teachers
     */
    public function getTeachers() {
        if ($this->getChildren() == null)
            return array();
        $model = Model::getInstance();
        $classes = $this->getClasses();
        $teachers = array();
        foreach ($classes as $class) {
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
     * @param limit int (most senior year group for EST plus one)
     *
     * @return array(Teacher,Child)
     */
    public function getTeachersOfAllChildren($limit) {
        $children = $this->getESTChildren($limit);
        $myArr = array();
        /** @var Student $child */
        foreach ($children as $child) {
            /** @var Teacher $teacher */
            foreach ($child->getTeachers() as $teacher) {
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
    public function getParentId() {
        return $this->parentId;
    }
    
    /**
     * @return array[String => mixed] returns all data of this class as array
     */
    public function getData() {
        return array_merge(parent::getData(), array("parentId" => $this->parentId, "children" => $this->children));
    }
    
    /** Returns class type
     *
     * @return string
     */
    public function getClassType() {
        return "Guardian";
    }
    
    /**
     *returns booked timeSlots
     *
     * @return array(Timestamp anfang)
     */
    public function getAppointments() {
        $model = Model::getInstance();
        $appointments = array();
        $appointmentData = $model->getAppointmentsOfParent($this->parentId);
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
    public function getAppointmentTeachers() {
        $model = Model::getInstance();
        $appointments = array();
        $appointmentData = $model->getAppointmentsOfParent($this->parentId);
        foreach ($appointmentData as $a) {
            $appointments[] = array("teacherId" => $a['teacherId']);
        }
        
        return $appointments;
    }
    
    /**
     *finds bookedTeacher Ids
     *
     * @return array(int)
     */
    public function getBookedTeachers() {
        $bookedTeachers = array();
        $appointments = $this->getAppointmentTeachers();
        $teachers = array();
        foreach ($appointments as $appointment) {
            $teachers[] = $appointment['teacherId'];
        }
        
        return $teachers;
    }
	
	
	    
    
}

/**
 *Teacher class as subclass of User class
 */
class Teacher extends User {
    /**
     * @var int lessonAmount (Deputat)
     */
    protected $lessonAmount;
    /**
     * @var string $ldapName
     */
    protected $ldapName;
    /**
     * @var string $untisName
     */
    protected $untisName;
    /**
     * @var string $shortName
     */
    protected $shortName;
    /**
     * @var string date of last update coverlessons
     */
    protected $vpInfoDate;
    /**
     * @var array(int) primary keys of current coverLessons
     */
    protected $coverLessonNrs = array();
    
    
    /**
     * Contructor of Teacher class
     *
     * @param int    $id userId
     * @param string $email
     */
    public function __construct($email, $teacherId, $rawData = null) {
        $nameData = Model::getInstance()->getTeacherNameByTeacherId($teacherId, $rawData);
        parent::__construct($teacherId, 2, $email, $nameData['name'], $nameData['surname']);
        if (isset($teacherId)) {
            $this->ldapName = Model::getInstance()->getTeacherLdapNameByTeacherId($teacherId, $rawData);
            $this->shortName = Model::getInstance()->getTeacherShortNameByTeacherId($teacherId, $rawData);
            $this->untisName = Model::getInstance()->getTeacherUntisNameByTeacherId($teacherId);
            $this->lessonAmount = Model::getInstance()->getTeacherLessonAmountByTeacherId($teacherId);
        }
        //Untis name could be "---" or "selbst", i.e. no corresponding entry in database
        if (isset($rawData["untisName"])) {
            $this->untisName = $rawData["untisName"];
        }
        
        //Kürzelfehler abfangen (bis DB aktualisiert)
        if (isset($rawData["shortName"])) {
            $this->shortName = $rawData["shortName"];
        }
		
		$this->dsgvo = Model::getInstance()->getDsgvoStatus($this);
        
    }
    
    /**
     * @return array[String => mixed] returns all data of this class as array
     */
    public function getData() {
        $parentData = parent::getData();
        
        return array_merge($parentData, array("lessonAmount" => $this->lessonAmount, "ldapName" => $this->ldapName));
    }
    
    /** Returns class type
     *
     * @return string
     */
    public function getClassType() {
        return "Teacher";
    }
    
    
    /**
     *returns Untisname
     *
     * @return String
     */
    public function getUntisName() {
        return $this->untisName;
    }
    
    /**
     *returns short Name
     *
     * @return String
     */
    public function getShortName() {
        return $this->shortName;
    }
    
    /**
     *Returns lesson amount of teacher (Deputat)
     *
     * @return int
     */
    public function getLessonAmount() {
        return $this->lessonAmount;
    }
    
    /**
     * returns date of last coeverlesson update
     *
     * @return string
     */
    public function getVpInfoDate() {
        return $this->vpInfoDate;
    }
    
    /**
     * returns currentCoverLessonNrs
     *
     * @return array(int)
     */
    public function getCoverLessonNrs() {
        return $this->coverLessonNrs;
    }
    
    /**
     * sets currentCoverLessonsNr
     *
     * @param array (int)
     */
    public function setCurrentCoverLessonNrs($arr) {
        $this->coverLessonNrs = $arr;
    }
    
    
    /**
     * returns status of mail service for changed coverlesson plan
     *
     * @return bool
     */
    public function getVpMailStatus() {
        return Model::getInstance()->getTeacherVpMailStatus($this->id);
    }
    
    /**
     * returns status of initial interface fo Cover Lesson View
     *
     * @return bool
     */
    public function getVpViewStatus() {
        return Model::getInstance()->getTeacherVpViewStatus($this->id);
    }
    
    
    /**
     *Returns required slots according to lessonAmount
     *
     * @return int
     */
    public function getRequiredSlots() {
        $HALFAMOUNT = 13.5;
        $MINAMOUNT = 12.5;
        $FULL = 10;
        $HALF = 5;
        $REDUCTION = 4;
        $amount = $FULL;
        $lessons = $this->getLessonAmount();
        if ($lessons < $HALFAMOUNT) {
            $amount = $FULL - $REDUCTION;
        }
        if ($lessons < $MINAMOUNT) {
            $amount = $HALF;
        }
        
        return $amount;
    }
    
    /**
     *returns missing slots for openday
     *
     * @return int
     */
    public function getMissingSlots() {
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
    public function getSlotListToAssign() {
        $slotList = array();
        $model = Model::getInstance();
        $assignedSlots = $this->getAssignedSlots();
        $allSlots = $model->getSlots();
        foreach ($allSlots as $slot) {
            foreach ($assignedSlots as $aSlot) {
                if ($slot['id'] == $aSlot) {
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
    public function setAssignedSlot($slotId) {
        $model = Model::getInstance();
        $model->setAssignedSlot($slotId, $this->id);
    }
    
    /**
     *returns AssignedSlots
     *
     * @return array(int)
     */
    public function getAssignedSlots() {
        $model = Model::getInstance();
        $assignedSlots = $model->getAssignedSlots($this->getId());
        
        return $assignedSlots;
    }
    
    /**
     *returns bookable Slots and booked Slots by a parent as array
     *
     * @return array(int bookingId, Timestamp anfang, Timestamp ende, int parentId)
     */
    public function getAllBookableSlots($parentId) {
        $model = Model::getInstance();
        
        return $model->getAllBookableSlotsForParent($this->id, $parentId);
    }
    
    /**
     * returns taught classes of teacher
     *
     * @return array(string)
     */
    public function getTaughtClasses() {
        $model = Model::getInstance();
        
        return $model->getTaughtClasses($this->id);
    }
	
	/**
	* get all taught students of teacher
	* @param string initial letters
	* @return array(Student Object)
	*/
	public function getAllTaughtPupilsByName($initial) {
			return Model::getInstance()->getAllTaughtPupils($this->id,$initial);
	}
    
    /**
     * returns appointments of teacher
     *
     * @return array(int);
     */
    public function getAppointmentsOfTeacher() {
        $model = Model::getInstance();
        
        return $model->getAppointmentsOfTeacher($this->id);
        
    }
    
    
    /*****************************************
     * functions for CoverLessonModule in Teacher
     *****************************************/
    /**
     * set date of last update in coverlessons
     *
     * @param string Datum
     */
    public function setVpInfoDate($datum) {
        $this->vpInfoDate = $datum;
    }
    
}

class Admin extends User {
    function __construct($id, $email) {
        parent::__construct($id, 0, $email);
    }
    
    /** Returns class type
     *
     * @return string
     */
    public function getClassType() {
        return "Admin";
    }
}

class StudentUser extends User {
    protected $class;
    protected $bday;
    protected $parent;
    
    
    /**
     * StudentUser constructor.
     *
     * @param int         $id
     * @param int         $name
     * @param string      $surname
     * @param null|string $class
     * @param null|string $bday
     * @param int         $parent
     * @param string      $courses
     */
    function __construct($id, $name, $surname, $class, $bday, $parent, $courses = null) {
        parent::__construct($id, 3, null, $name, $surname);
        //$this->id = $id;//entered 20181209 when trying to realise appLogin
		$this->class = $class;
        $this->bday = $bday;
        $this->parent = $parent;
        //$this->courses = $courses;  -- 
		$this->dsgvo = Model::getInstance()->getDsgvoStatus($this);
    }
    
    /**
     * @return string
     */
    public function getClass() {
        return $this->class;
    }
    
    /**
     * @return string
     */
    public function getBday() {
        return $this->bday;
    }
    
    /**
     * @return int
     */
    public function getParent() {
        return $this->parent;
    }
    
    /**
     * @return string
     */
    public function getCourses() {
        return Model::getInstance()->getStudentCourses($this->id);
    }
    
    public function getClassType() {
        return "Student";
    }
    
    /**
     * @return array[String => Data] used for creating __toString and jsonSerialize
     */
    public function getData() {
        
        return array_merge(parent::getData(), array("class" => $this->class, "bday" => $this->bday, "eid" => $this->parent, "courses" => $this->getCourses()));
    }
    
}

/**
 * Class Student
 */
class Student extends Printable {
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
    /**
     * @var array(String) student's courses
     */
    protected $courses;
    
    public function __construct($id, $class, $surname, $name, $bday, $eid = null) {
        $this->id = intval($id);
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
    public function getId() {
        return $this->id;
    }
    
    /**
     * @return string
     */
    public function getClass() {
        return $this->class;
    }
    
    /**
     * @return string
     */
    public function getSurname() {
        return $this->surname;
    }
    
    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }
    
    /**
     * @return int
     */
    public function getEid() {
        return $this->eid;
    }
    
    public function getFullName() {
        return $this->getName() . " " . $this->getSurname();
    }
    
    /**
     * @return string
     */
    public function getBday() {
        return $this->bday;
    }
    
    /**
     * @return array(String)
     */
    public function getCourses() {
        return $this->courses;
    }
    
    /**
     * @return array[String => Data] used for creating __toString and jsonSerialize
     */
    public function getData() {
        return array("id" => $this->id, "type" => 3, "class" => $this->class, "surname" => $this->surname, "name" => $this->name, "eid" => $this->eid, "bday" => $this->bday, "email" => null);
    }
    
    /** Returns class type
     *
     * @return string
     */
    public function getClassType() {
        return "Student";
    }
	
	/**
	* get absence state
	* return boolean
	*/
	public function getAbsenceState() {
		return Model::getInstance()->getStudentAbsenceState($this->getId());
	}
    
    /**
     * Get all teachers teaching this student
     *
     * @return array(Teachers)
     */
    public function getTeachers() {
        $model = Model::getInstance();
        $teachers = $model->getTeachersByClass($this->getClass());
        if ($teachers == null)
            return array();
        sort($teachers);
        
        return $teachers;
    }
    
    /**
     * find all Courses of Student if applicable
     *
     */
    private function getAllCourses() {
        $this->courses = Model::getInstance()->getCoursesOfStudent($this->Id);
    }
	
	/**
	* get ASV_ID of student
	* @return string 
	*/
	public function getASVId() {
			return Model::getInstance()->getASVId($this->id);
	}
	
	

}

?>