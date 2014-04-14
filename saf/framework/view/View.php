<?php
namespace SAF\Framework;

use SAF\Framework\Controller\Getter;
use SAF\Framework\Plugin\Configurable;
use SAF\Framework\Tools\Current;
use SAF\Framework\Tools\Namespaces;

/**
 * The View class offers static methods to call views from the application main view engine
 */
class View implements Configurable
{
	use Current { current as private pCurrent; }

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $configuration array
	 */
	public function __construct($configuration)
	{
		$class_name = $configuration['class'];
		unset($configuration['class']);
		View::current(new $class_name($configuration));
	}

	//--------------------------------------------------------------------------------------- current
	/**
	 * @param $set_current View\Engine
	 * @return View\Engine
	 */
	public static function current(View\Engine $set_current = null)
	{
		return self::pCurrent($set_current);
	}

	//--------------------------------------------------------------------------------------- getView
	/**
	 * @param $view_name     string
	 * @param $feature_names string[]
	 * @return string[] [$view_class_name, $view_method_name]
	 */
	private static function getView($view_name, $feature_names)
	{
		$view_engine_name = get_class(View::current());
		$view_engine_name = Namespaces::shortClassName(Namespaces::of($view_engine_name));
		foreach ([$view_engine_name . '_View', 'View'] as $suffix) {
			foreach ($feature_names as $feature_name) {
				list($class, $method) = Getter::get($view_name, $feature_name, $suffix, 'php');
				if (isset($class)) break 2;
			}
		}
		/** @noinspection PhpUndefinedVariableInspection if $class is set, then $method is set too */
		return isset($class) ? [$class, $method] : [$view_name, reset($feature_names)];
	}

	//----------------------------------------------------------------------------------- executeView
	/**
	 * @param $view             string
	 * @param $view_method_name string
	 * @param $parameters       array
	 * @param $form             array
	 * @param $files            array
	 * @param $class_name       string
	 * @param $feature_name     string
	 * @return mixed
	 */
	private static function executeView(
		$view, $view_method_name, $parameters, $form, $files, $class_name, $feature_name
	) {
		$view_object = Builder::create($view);
		return $view_object->$view_method_name(
			$parameters, $form, $files, $class_name, $feature_name
		);
	}

	//------------------------------------------------------------------------------------------ link
	/**
	 * Generates a link for to an object and feature, using parameters if needed
	 *
	 * @param $object     object|string linked object or class name
	 * @param $feature    string linked feature name
	 * @param $parameters string|string[]|object|object[] optional parameters list
	 * @param $arguments  string|string[] optional arguments list
	 * @return string
	 */
	public static function link($object, $feature = null, $parameters = null, $arguments = null)
	{
		return self::current()->link($object, $feature, $parameters, $arguments);
	}

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters   array  Parameters for the view. The first must be the context object.
	 * @param $form         array  Form parameters
	 * @param $files        array  Files parameters
	 * @param $class_name   string The context class name (class of the first parameter)
	 * @param $feature_name string The feature class name
	 * @return mixed
	 */
	public static function run($parameters, $form, $files, $class_name, $feature_name)
	{
		$feature_names = (isset($parameters['feature']) && ($parameters['feature'] != $feature_name))
			? [$parameters['feature'], $feature_name]
			: [$feature_name];
		list($view_name, $view_method_name) = self::getView($class_name, $feature_names);
		return self::executeView(
			$view_name, $view_method_name, $parameters, $form, $files, $class_name, $feature_name
		);
	}

}
