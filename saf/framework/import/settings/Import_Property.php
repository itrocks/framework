<?php
namespace SAF\Framework\Import\Settings;

/**
 * Import property
 */
class Import_Property
{

	//---------------------------------------------------------------------------------------- $class
	/**
	 * @var string
	 */
	public $class;

	//----------------------------------------------------------------------------------------- $name
	/**
	 * @var string
	 */
	public $name;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class string
	 * @param $name  string
	 */
	public function __construct($class = null, $name = null)
	{
		if (isset($class)) $this->class = $class;
		if (isset($name))  $this->name  = $name;
	}

}
