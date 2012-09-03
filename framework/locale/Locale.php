<?php
namespace Framework;

class Locale
{

	private static $instance;

	//----------------------------------------------------------------------------------- __construct
	private function __construct()
	{
	}

	//----------------------------------------------------------------------------------- getInstance
	public function getInstance()
	{
		if (!Locale::$instance) {
			Locale::$instance = new Locale();
		}
		return Locale::$instance;
	}

}
