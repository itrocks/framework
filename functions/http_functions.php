<?php

//-------------------------------------------------------------------------------------------- cors
function cors() : void
{
	static $already = false;
	if ($already) {
		return;
	}
	$already = true;
	if (isset($_SERVER['HTTP_ORIGIN'])) {
		header("Access-Control-Allow-Origin: $_SERVER[HTTP_ORIGIN]");
		header('Access-Control-Allow-Credentials: true');
		header('Access-Control-Max-Age: 86400');
	}
	if (isset($_SERVER['REQUEST_METHOD']) && ($_SERVER['REQUEST_METHOD'] === 'OPTIONS')) {
		if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
			header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
		}
		if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
			header("Access-Control-Allow-Headers: $_SERVER[HTTP_ACCESS_CONTROL_REQUEST_HEADERS]");
		}
		exit(0);
	}
}
