<?php
namespace SAF\Framework;

class Locale
{

	//----------------------------------------------------------------------------------- __construct
	private function __construct() {}

	//----------------------------------------------------------------------------------- getInstance
	public function getInstance()
	{
		static $instance = null;
		if (!isset($instance)) {
			$instance = new Locale();
		}
		return $instance;
	}

}
