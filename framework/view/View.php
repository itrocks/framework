<?php
namespace SAF\Framework;

use SAF\Framework\Tools\Current;
use SAF\Framework\Tools\Names;
use SAF\Framework\Tools\Namespaces;
use SAF\Plugins;

/**
 * The View class offers static methods to call views from the application main view engine
 */
class View implements Plugins\Configurable
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

	//------------------------------------------------------------------------------ getPossibleViews
	/**
	 * @param $class_name    string
	 * @param $feature_names string|string[]
	 * @return string[]
	 */
	public static function getPossibleViews($class_name, $feature_names)
	{
		if (!is_array($feature_names)) {
			$feature_names = [$feature_names];
		}
		$class_name = Namespaces::shortClassName($class_name);
		$view_engine_name = Namespaces::shortClassName(get_class(View::current()));
		$view_engine_name = substr($view_engine_name, 0, strrpos($view_engine_name, '_View_Engine'));
		$feature_classes = [];
		foreach ($feature_names as $feature_name) {
			$feature_classes[$feature_name] = Names::methodToClass($feature_name);
		}
		$views1 = [];
		$views2 = [];
		$views3 = [];
		$views4 = [];
		$namespaces = Application::current()->getNamespaces();
		foreach ($namespaces as $namespace) {
			$class = $namespace . BS . $class_name;
			while ($class) {
				$i = strrpos($class, BS) + 1;
				$view = $namespace . BS . $view_engine_name;
				foreach ($feature_classes as $feature_name => $feature_class) {
					$views2[] = [$view . '_' . $feature_class . '_View', 'run'];
					$views3[] = [$view . '_Default_View', $feature_name];
				}
				$views4[] = [$view . '_Default_View', 'run'];
				$view .= '_' . substr($class, $i);
				foreach ($feature_classes as $feature_name => $feature_class) {
					$views1[] = [$view . '_' . $feature_class . '_View', 'run'];
					$views1[] = [$view . '_View', $feature_name];
				}
				$class = get_parent_class($class);
			}
		}
		$views = array_merge($views1, $views2, $views3, $views4);
		return $views;
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
		$features = isset($parameters['feature'])
			? [$parameters['feature'], $feature_name]
			: $feature_name;
		foreach (self::getPossibleViews($class_name, $features) as $call) {
			list($view, $view_method_name) = $call;
			if (@method_exists($view, $view_method_name)) {
				return self::executeView(
					$view, $view_method_name, $parameters, $form, $files, $class_name, $feature_name
				);
			}
		}
		return '';
	}

}
