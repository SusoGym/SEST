<?php

/**
 *
 */
class Controller
{

  function __construct($input)
  {
    if (isset($input['type'])) {
      if ($input['type'] == "login") {
        # if login is valid, login, then redirect
      } elseif ($input['type'] == "booking") {
        if ($input['booking_action'] == "add") {
          # create new Booking, send to Model
        } elseif ($input['booking_action'] == "delete") {
          # exec
        }
      }

    } else {
      # login form
    }
  }
}

?>
