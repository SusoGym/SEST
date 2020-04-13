<?php
/************************************
********controller for webapi********
*******webapi only receives data*****
****from web applications and *******
***returns json strings if at all****
***authentication is realized via****
*****an api token which will be *****
****passed by the application******** 
************************************/

class Controller {
/*
* var 
*/
private $model;
private $input = array();

/**
* constructor
* @param input array()
*/

public function __construct($input) {
$this->model = new WebApiModel();
$token = null;
//check authentication
if (isset($_SESSION['token']) ) {
	$token = $_SESSION['token']; 
	} else if (isset($input['tkn'])) {
	$token = $input['tkn'];
	} else {
	$return = array("message" => "authentication failed!");
	$this->sendStatusMessage($return);
	}
if ($this->checkTokenAuth($token) ) {
	$this->handleInput($input);
    } else {
        $return = array("message" => "authentication failed!");
        $this->sendStatusMessage($return);
    }

}

/**
* handle inputs
* @param string
*/
private function handleInput($input) {
$return = null;
if (isset($input['type']) ) {
    switch ($input['type']) {
        case "event":
			if(isset($input['eventdata'])) {
			$return = $this->updateEvents($input['eventdata']);
			} else {
			$return = array("message" => "no data");
			}
            break;
        default:
            $return = array("message" => "nothing to do");
            break;
        }
		
    } else {
        $return = array("message" => "nothing to do");
        

    }
$this->sendStatusMessage($return);

}



/**
* check authentication
* @param token string
* @return boolean
*/
private function checkTokenAuth($token) {
    $customer = $this->model->checkTokenAuth($token);
    return ($customer != null) ? true : false;
	}


/**
* update events
* @param string
*/
private function updateEvents($events) {
$eventArray = json_decode($events,true);
var_dump($eventArray) ; //nur debugging
$this->model->addEventsToDB($eventArray);
$return = array("message" => "events updated");
return $return;
}



/**
* sending status message to calling application
* @param array
*/
private function sendStatusMessage($msg) {
   die(json_encode($msg) );
}

}
?>
