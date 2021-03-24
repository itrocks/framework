<?php

if (!isset($argv[0])) {
	$argv[0] = __FILE__;
}
if (!isset($argv[1])) {
	$argv[1] = '/ITRocks/Framework/Application/blank';
}

require_once __DIR__ . '/../console_script.php';
