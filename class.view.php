<?php

/**
 * View Class
 */
class view
{

  function __construct($template)
  {
    include("templates/$template.php");
  }
}


?>
