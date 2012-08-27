<?php

class Environment
{

	/**
	 * @var Environment
	 */
	private static $current;

	//----------------------------------------------------------------------------------- __construct
	public function __construct()
	{
		if (!Environment::$current) {
			$current = $this;
		} 
	}

	//------------------------------------------------------------------------------------ getCurrent
	/**
	 * @return Environment
	 */
	public static function getCurrent()
	{
		return Environment::$current;
	}

	//------------------------------------------------------------------------------------ setCurrent
	/**
	 * @param Environment $environment
	 */
	public static function setCurrent($environment)
	{
		Environment::$current = $environment;
	}

}
