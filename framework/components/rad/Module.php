<?php
namespace SAF\Framework\RAD;

use SAF\Framework\Component;

/**
 * A module depends on an application, and may not be used without it or one of it's descendant
 *
 * A module can depend on other modules
 */
class Module
{
	use Component;

	//----------------------------------------------------------------------------------------- $name
	/**
	 * @var string
	 */
	public $name;
	//---------------------------------------------------------------------------------- $sub_modules
	/**
	 * @link Collection
	 * @var Module[]
	 */
	public $sub_modules;

	//---------------------------------------------------------------------------------- $application
	/**
	 * @link Object
	 * @var Application
	 */
	public $application;

	//--------------------------------------------------------------------------------- $dependencies
	/**
	 * @link Map
	 * @var Module[]
	 */
	public $dependencies;

	//----------------------------------------------------------------------------------- $data_class
	/**
	 * @link Collection
	 * @var Data_Class[]
	 */
	public $data_classes;

}
