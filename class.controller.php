<?php

/**
 *
 */
class Controller
{

  function __construct($input)
  {
	$model = new Model();
    if (isset($input['type'])) {
	  if ($input['type'] == "login") {
	    # validate, then set session var
	  } elseif ($input['type'] == "booking") {
        if ($input['booking_action'] == "add") {
          # create new Booking, send to Model
        } elseif ($input['booking_action'] == "delete") {
          # exec
        }
      } elseif ($input['type'] == "register") {
		# check, then write into database, then login (session var...)
	  }

    } else {
      $this->tpl = "login";
	  $this->display();
    }
  }
  
  function display()
  {
	$view = new View($this->tpl);
  }

}
?>
