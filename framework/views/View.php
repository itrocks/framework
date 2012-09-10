<?php
namespace SAF\Framework;

abstract class View
{

	/**
	 * @var View_Engine
	 */
	private static $current_view_engine;

	//------------------------------------------------------------------------------------ getCurrent
	/**
	 * @return View_Engine
	 */
	public static function getCurrent()
	{
		return View::$current_view_engine;
	}

	//------------------------------------------------------------------------------ getPossibleViews
	public static function getPossibleViews($class_name, $feature_name)
	{
		$view_engine_name = Namespaces::shortClassName(get_class(View::getCurrent()));
		$view_engine_name = substr($view_engine_name, 0, strrpos($view_engine_name, "_View_Engine"));
		$feature_class = Names::methodToClass($feature_name);
		return array(
			array($view_engine_name . "_" . $class_name . "_" . $feature_class, "run"),
			array($view_engine_name . "_" . $class_name, $feature_name),
			array($view_engine_name . "_" . $feature_class, "run"),
			array($view_engine_name . "_Default_View", $feature_name),
			array($view_engine_name . "_Default_View", "run")
		);
	}

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param Controller_Parameters $parameters
	 * @param array  $form
	 * @param array  $files
	 * @param string $class_name
	 * @param string $feature_name
	 */
	public static function run($parameters, $form, $files, $class_name, $feature_name)
	{
		foreach (View::getPossibleViews($class_name, $feature_name) as $call) {
			list($view_class_name, $view_method_name) = $call;
			foreach (Application::getNamespaces() as $namespace) {
				$view = $namespace . "\\" . $view_class_name;
				if (@method_exists($view, $view_method_name)) {
					$view_object = new $view();
					$view_object->$view_method_name($parameters, $form, $files, $class_name, $feature_name);
					break;
				}
			}
		} 
	}

	//------------------------------------------------------------------------------------ setCurrent
	/**
	 * @param View_Engine $current_view
	 */
	public static function setCurrent($current_view_engine)
	{
		View::$current_view_engine = $current_view_engine;
	}

}
