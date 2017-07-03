<?php

/* 
* Klasse Newsletter
*/

class Newsletter{

	private $id;
	private $newsDate;
	private $newsText;
	private $lastChanged;
	private $sendDate;
	private $schoolYear;
	
	/***********************
	****GETTER und SETTER***
	***********************/
	/*
	* getId
	* return int
	*/
	public function getId(){
		return $this->id;
		}
	/*
	* getNewsdate
	* return int
	*/
	public function getNewsDate(){
		return $this->newsDate;
		}
	/*
	* getnewsText
	* return String
	*/
	public function getNewsText(){
		return $this->newsText;
		}
	/*
	* getlastchanged
	* return int
	*/
	public function getLastChanged(){
		return $this->lastChanged;
		}
	/*
	* get sendDate
	* return int
	*/
	public function getSendDate(){
		return $this->sendDate;
		}
	/*
	* getSchoolYear
	* return String
	*/
	public function getSchoolYear(){
		return $this->schoolYear;
		}
		
	/*
	* setId
	* @param int
	*/
	private function setId($value){
		$this->id = $value;
		}
	/*
	* setNewsDate
	* @param int
	*/
	private function setNewsDate($value){
		$this->newsDate = $value;
		}
	/*
	* setnewsText
	* @param String
	*/
	private function setNewsText($value){
		$this->newsText = $value;
		}
	/*
	* setlastChanged
	* @param int
	*/
	private function setLastChanged($value){
		$this->lastChanged = $value;
		}
	/*
	* setsendDate
	* @param int
	*/
	private function setSendDate($value){
		$this->sendDate = $value;
		}
	/*
	* setSchoolYear
	* @param String
	*/
	private function setSchoolYear($value){
		$this->schoolYear = $value;
		}
	
	/*********************
	***Konstruktoren******
	*********************/
	
	/**
	* Konstruktor nach Übergabe von id
	* param int id
	*/
	public function createFromId ($id) {
		$data = Model::getNewsletterData($id);
		$this->id = $id;
		$date = $data["publishdate"];
		$date = $date[6].$date[7].'.'.$date[4].$date[5].'.'.$date[0].$date[1].$date[2].$date[3];
		
		$this->setNewsDate($date);
		$this->setNewsText($data["text"]);
		$this->setLastChanged($data["lastchanged"]);
		$this->setSendDate($data["sent"]);
		$this->setSchoolYear($data["schoolyear"]);
		}
	
	/**
	* Konstruktor nach Übergabe von POST Werten
	* @param newsDate
	* @param newsText
	* @param int id
	*/
	public function createFromPOST($newsdate,$newstext,$id) {
		$datearr = explode(".",$newsdate);
		$dyr = $datearr[2];
		$dmt = $datearr[1];
		$dd	= $datearr[0];
		$publishdate = $dyr.$dmt.$dd;
		($dmt > 8) ? $schoolYear = $dyr."/".($dyr+1) : $schoolYear = ($dyr-1)."/".$dyr;
		$this->setNewsDate($publishdate);
		//trimming whitespaces
		$newstext = trim($newstext);
		//Escaping quotes in text
		$newstext = str_replace("'","\'",$newstext);
		$newstext = str_replace('"','\"',$newstext);
		$this->setNewsText($newstext);
		$this->setSendDate(null);
		$this->setSchoolYear($schoolYear);
		if (isset($id)) {
			Model::UpdateNewsInDB($id,$publishdate,$newstext,$schoolYear);
			$this->setId = $id;
			}
		else{
			$this->setId(Model::InsertNewsIntoDB($publishdate,$newstext,$schoolYear));
			}
		
		}
	
	/**
	* enter sendDate
	*/
	public function UpdateSendDate(){
		Model::setNewsSendDate($this->id);
		}
	
	/**
	* make Text to view
	* @param bool html 
	* @return String
	*/
	public function	makeViewText($html = true) {
	if ($html) {
		$text = Model::makeHTMLNewsletter($this);
		}
	else {
		$text = Model::makePlainTextNewsletter($this);
		}
				
		
	return $text;	
	}
	
		
	
}