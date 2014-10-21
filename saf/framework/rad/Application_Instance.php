<?php
namespace SAF\Framework\RAD;

use SAF\Framework\RAD\Plugin\Active_Plugin;

/**
 * An application instance is a particular use of an existing application
 *
 * It uses an application and some of its modules and a selection of compliant plugins
 *
 * @set RAD_Applications_Instances
 */
class Application_Instance
{

	//---------------------------------------------------------------------------------- $application
	/**
	 * @link Object
	 * @mandatory
	 * @var Application
	 */
	public $application;

	//-------------------------------------------------------------------------------------- $plugins
	/**
	 * All the application instance active plugins
	 *
	 * @link Collection
	 * @var Active_Plugin[]
	 */
	public $plugins;

}
