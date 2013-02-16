<?php
namespace SAF\Framework;

abstract class View
{
	use Current { current as private pCurrent; }

	//--------------------------------------------------------------------------------------- current
	/**
	 * @param $set_current View_Engine
	 * @return View_Engine
	 */
	public static function current(View_Engine $set_current = null)
	{
		return self::pCurrent($set_current);
	}

	//------------------------------------------------------------------------------ getPossibleViews
	/**
	 * @param $class_name string
	 * @param $feature_name string
	 * @return string[]
	 */
	public static function getPossibleViews($class_name, $feature_name)
	{
		$class_name = Namespaces::shortClassName($class_name);
		$view_engine_name = Namespaces::shortClassName(get_class(View::current()));
		$view_engine_name = substr($view_engine_name, 0, strrpos($view_engine_name, "_View_Engine"));
		$feature_class = Names::methodToClass($feature_name);
		$views = array();
		$namespaces = Application::getNamespaces();
		foreach ($namespaces as $namespace) {
			$class = $namespace . "\\" . $class_name;
			while ($class) {
				$i = strrpos($class, "\\") + 1;
				$view = $namespace . "\\" . $view_engine_name . "_" . substr($class, $i);
				$views[] = array($view . "_" . $feature_class, "run");
				$views[] = array($view, $feature_name);
				$class = get_parent_class($class);
			}
		}
		foreach ($namespaces as $namespace) {
			$view = $namespace . "\\" . $view_engine_name;
			$views[] = array($view . "_" . $feature_class, "run");
			$views[] = array($view . "_Default_View", $feature_name);
			$views[] = array($view . "_Default_View", "run");
		}
		return $views;
	}

	//------------------------------------------------------------------------------------------ link
	/**
	 * @param $object     object|string object or class name
	 * @param $parameters string|string[] string or array : parameters list (feature and other parameters)
	 * @return string
	 */
	public static function link($object, $parameters = null)
	{
		return self::current()->link($object, $parameters);
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
		foreach (static::getPossibleViews($class_name, $feature_name) as $call) {
			list($view, $view_method_name) = $call;
			if (@method_exists($view, $view_method_name)) {
				$view_object = new $view();
				return $view_object->$view_method_name(
					$parameters, $form, $files, $class_name, $feature_name
				);
			}
		}
	}

}
