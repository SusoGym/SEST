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
        case "usrchk":
            //check for all invalid users, i.e. user without children or user without verification (not yet in use)
            $msg = $this->checkUsersAndAct();
            $return = array("message" => $msg);
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
//var_dump($eventArray) ; //nur debugging
$this->model->addEventsToDB($eventArray);
$return = array("message" => "events updated");
return $return;
}

/**
 * checking for unused users
 * @return string
 */
private function checkUsersAndAct(){
    $users = $this->model->getUsersWithoutKids();
    $unconfirmedUsers = $this->model->getUsersWithoutConfirmedRegistration();
    //write actions to log
    $fh=fopen("usrchk.log",'a');
    $checkingTime = date("d.m.Y H:i:s");
    fputs($fh,"******checking for users at ".$checkingTime." *******\r\n");
    fputs($fh,"** ".count($users)." without registered kids!**\r\n");
    $i = 1;
    foreach ($users as $usr){
        $addon = "";
        if ($usr['todelete'] == true) {
            $addon = " -- WILL BE REMOVED!";
            $this->model->deleteUsers($usr['id'],$usr['eid']);
            }
    $line = $i." - User with ID ".$usr['id']."[eid = ".$usr['eid']."]: ".$usr['name'].", ".$usr['vorname'].
    " (".$usr['mail'].") - registriert am: ".$usr['registered'].$addon."\r\n"; 
    fputs($fh,$line);
    $i++;
    
    
    }
    fputs($fh,"******checking for users at ".$checkingTime." *******\r\n");
    fputs($fh,"** ".count($unconfirmedUsers)." without complete registration -- ALL USERS WILL BE REMOVED!**\r\n");
    $i = 1;
    foreach ($unconfirmedUsers as $usr){
    $line = $i." - User with ID ".$usr['id']."[eid = ".$usr['eid']."]: ".$usr['name'].", ".$usr['vorname']." (".$usr['mail'].") - registriert am: ".$usr['registered']."\r\n"; 
    fputs($fh,$line);
    $i++;
    $this->model->deleteUsers($usr['id'],$usr['eid']);
    }
    fclose($fh);
    //trigger email report
    $this->sendWeeklyUserReport($users,$unconfirmedUsers,"an@email.com");

    return "unused users detected and deleted. See log-file.";
}


/**
* sending status message to calling application
* @param array
*/
private function sendStatusMessage($msg) {
   die(json_encode($msg) );
}



/**
 * sending a weekly report to admin user 
 * reporting about users without a confirmed registration oer without kids
 * the report is triggered by a remote server cronjob
 * @param array users without kids
 * @param array users without confrimed registration
 * @param string
 */
private function sendWeeklyUserReport($users,$unconfirmedUsers,$email){
    require_once("../PHPMailer.php");
    $now = date("d.m.Y H:i:s");
    $text = "Your weekly report on Suso-Intern-User State, triggered at <b>".$now."</b> :<br/><br/><b>
    The following users have not registered kids, yet. They will be automatically removed after 60 days:</b><br/><br/>";
    $i = 1;
    foreach ($users as $usr){
        $addon = ($usr['todelete'] == true) ? " -- WILL BE REMOVED!" : "";
        $text .= $i." - User with ID ".$usr['id']."[eid = ".$usr['eid']."]: ".$usr['name'].", ".$usr['vorname'].
        " (".$usr['mail'].") - registriert am: ".$usr['registered'].$addon."<br/>"; 
        $i++;
    }
    $text .= '<br/><br/><b>The following users have not confirmed their registration for more than 24 hrs and will be removed: </b><br/><br/>';
    $i = 1;
    foreach ($unconfirmedUsers as $usr){
        $text .= $i." - User with ID ".$usr['id']."[eid = ".$usr['eid']."]: ".$usr['name'].", ".$usr['vorname']." (".$usr['mail'].") - registriert am: ".$usr['registered']."<br/>"; 
        $i++;
        }
    $content = $text;

    //sending emails
    $phpmail = new PHPMailer();
    $phpmail->setFrom("noreply@suso.konstanz.de", "Suso-Intern");
    $phpmail->CharSet = "UTF-8";
    $phpmail->isHTML();
    $phpmail->AddAddress($email);
    $phpmail->Subject = date('d.m.Y - H:i:s') . "Suso-Intern - weekly user-report";
    $phpmail->Body = $content;
        
    $send = true;
    
    //Senden
    if (!$phpmail->Send()) {
        $send = false;
    } 
    
    return $send;
}
}
?>
