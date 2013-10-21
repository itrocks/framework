<?php
namespace SAF\Framework\RAD;

/**
 * An application instance is a particular use of an existing application
 *
 * It uses an application and some of its modules and a selection of compliant plugins
 */
class Application_Instance
{

	//---------------------------------------------------------------------------------- $application
	/**
	 * @link Object
	 * @var Application
	 */
	public $application;

	//-------------------------------------------------------------------------------------- $modules
	/**
	 * The application modules that are active
	 *
	 * @link Map
	 * @var Module[]
	 */
	public $modules;

	//-------------------------------------------------------------------------------------- $plugins
	/**
	 * @link Map
	 * @var Plugin[]
	 */
	public $plugins;

}
