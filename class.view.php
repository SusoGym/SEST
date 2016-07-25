<?php

/**
 * View Class
 */
class View
{

  function __construct($template, $data)
  {
    include("templates/$template.php");
    $this->data = $data;
  }
}


?>
