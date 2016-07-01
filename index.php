<?php

session_start();

require("class.controller.php", "class.model.php", "class.view.php");

$input = array_merge($_GET, $_POST);
Controller $control = new Controller($input);

?>
