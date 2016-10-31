<?php

/**
 * View Class
 */
class View
{

  public function __construct($template, $data)
  {
    $this->data = $data;
    include("templates/$template.php");
  }
}


?>
