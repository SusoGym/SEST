<?php

/**
 * View Class
 */
class view
{

  function __construct($template, $data)
  {
    include("templates/$template.php");
    $this->data = $data;
  }
}


?>
