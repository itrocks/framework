<?php
namespace SAF\Framework\RAD\Applications;

use SAF\Framework;
use SAF\Framework\Builder;
use SAF\Framework\Dao;
use SAF\Framework\Dao\Func;
use SAF\Framework\RAD\Plugins\Plugin;
use SAF\Framework\Tools\Names;

/**
 * Application
 *
 * A set of plugins
 *
 * @representative name
 */
class Application
{

	//---------------------------------------------------------------------------------------- $class
	/**
	 * @mandatory
	 * @var string
	 */
	public $class;

	//----------------------------------------------------------------------------------------- $name
	/**
	 * @mandatory
	 * @var string
	 */
	public $name;

	//--------------------------------------------------------------------------------------- $parent
	/**
	 * @link Object
	 * @var Application
	 */
	public $parent;

	//-------------------------------------------------------------------------------------- $plugins
	/**
	 * @link Collection
	 * @var Plugin[]
	 * @widget Plugins
	 */
	public $plugins;

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return strval($this->name);
	}

	//--------------------------------------------------------------------------------------- current
	/**
	 * Returns the current application
	 * This is the application with the higher level : the application of your current instance
	 *
	 * @param $class_name string If set, the application class name we are looking for
	 * @return self
	 */
	public static function current($class_name = null)
	{
		if (!$class_name) {
			$class_name = get_class(Framework\Application::current());
		}
		/** @var $applications Application[] */
		$applications = Dao::search(['class' => $class_name], Application::class);
		if ($applications) {
			$application = reset($applications);
		}
		else {
			/** @var $application Application */
			$application        = Builder::create(Application::class);
			$application->class = $class_name;
			$application->name  = Names::classToDisplay(lLastParse($class_name, BS));
			if ($parent_class = get_parent_class($class_name)) {
				$application->parent = self::current($parent_class);
			}
			// TODO application plugins scan and initialization here
			Dao::write($application);
		}
		return $application;
	}

}
