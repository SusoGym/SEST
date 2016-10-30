<?php

/**
 * View Class
 */
class View
{

/**
*@Constant path to template files 
*/
private $PATH='templates';	


/**
  * @var string Header Informationen
  */
  private $action;

/**
*@var array various data to be used in view
*/
private $data; 

/**
*@var string filename 
*/
private $file; 
  
/**
*Template Dateien werden geladen
*/	
public function loadTemplate($template){
$file = $this->PATH . DIRECTORY_SEPARATOR . $template . '.php';  
$exists = file_exists($file); 	
if ($exists) {
	include($file);	
	}
else{
	return 'could not find template';  	
	}	
}


/**
*set Data
*@param array*/
public function setViewData($data){
	$this->data=$data;
	}



/**
*set filename
*@param array
*/
public function setFile($file){
	$this->file=$file;
	}

/**
*set header
*@param array
*/
public function setHeader($string){
	$this->action=$string;
	}

	
}


?>
