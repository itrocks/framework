<?php

require "Framework/controllers/Main_Controller.php";
Main_Controller::getInstance()->run($_SERVER["PATH_INFO"], $_GET, $_POST, $_FILES);

echo "<pre>" . print_r($GLOBALS, true) . "</pre>";
