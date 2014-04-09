<?php
namespace SAF\Framework\RAD;

/**
 * Application
 *
 * A set of modules, that can get modules from a parent application
 */
class Application
{

	//----------------------------------------------------------------------------------------- $name
	/**
	 * @var string
	 */
	public $name;

	//--------------------------------------------------------------------------------------- $parent
	/**
	 * @var Application
	 */
	public $parent;

	//-------------------------------------------------------------------------------------- $modules
	/**
	 * @var Module[]
	 */
	public $modules;

}
