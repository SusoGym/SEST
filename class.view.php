<?php

/**
 * View Class
 */
class View
{

  function __construct($template, $data)
  {
    $this->data = $data;
    include("templates/$template.php");
  }
}


?>
