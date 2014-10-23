<?php
namespace SAF\Framework\RAD\Applications\Instance;

use SAF\Framework\Builder;
use SAF\Framework\Dao;
use SAF\Framework\RAD\Applications\Application;

/**
 * An application instance is a particular use of an existing application
 *
 * It uses an application and some of its modules and a selection of compliant plugins
 *
 * @representative application.name
 */
class Instance
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
	 * All the application instance active plugins, taken from the application and its parents
	 *
	 * @link Collection
	 * @var Active_Plugin[]
	 */
	public $plugins;

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return strval($this->application->name);
	}

	//---------------------------------------------------------------------------------- applications
	/**
	 * Gets all applications, starting from the main application to its most far parent
	 *
	 * @return Application[]
	 */
	public function applications()
	{
		$applications = [];
		$application = $this->application;
		while ($application) {
			$applications[] = $application;
			$application = $application->parent;
		}
		return $applications;
	}

	//--------------------------------------------------------------------------------------- current
	/**
	 * Return current application instance
	 * You always can have only one application instance : the running one
	 *
	 * @return self
	 */
	public static function current()
	{
		/** @var $instances Instance[] */
		$instances = Dao::readAll(Instance::class);
		if ($instances) {
			$instance = reset($instances);
		}
		else {
			/** @var $instance Instance */
			$instance = Builder::create(Instance::class);
			$instance->application = Application::current();
			Dao::write($instance);
		}
		return $instance;
	}

}
