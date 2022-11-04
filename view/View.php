<?php
namespace ITRocks\Framework;

use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Getter;
use ITRocks\Framework\Plugin\Configurable;
use ITRocks\Framework\Tools\Current;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\Tools\Namespaces;
use ITRocks\Framework\View\Html\Template;

/**
 * The View class offers static methods to call views from the application main view engine
 */
class View implements Configurable
{
	use Current { current as private pCurrent; }

	//---------------------------------------------------------------------------------------- TARGET
	/**
	 * Constants common to all views
	 */
	const TARGET = 'target';

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $configuration array
	 */
	public function __construct(mixed $configuration)
	{
		$class_name = $configuration[Configuration::CLASS_NAME];
		unset($configuration[Configuration::CLASS_NAME]);
		View::current(new $class_name($configuration));
	}

	//--------------------------------------------------------------------------------------- current
	/**
	 * @param $set_current View\Engine|null
	 * @return ?View\Engine
	 */
	public static function current(View\Engine $set_current = null) : ?View\Engine
	{
		/** @noinspection PhpIncompatibleReturnTypeInspection */
		return self::pCurrent($set_current);
	}

	//----------------------------------------------------------------------------------- executeView
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $view             string
	 * @param $view_method_name string
	 * @param $parameters       array
	 * @param $form             array
	 * @param $files            array[]
	 * @param $class_name       string
	 * @param $feature_name     string
	 * @return ?string
	 */
	private static function executeView(
		string $view, string $view_method_name, array $parameters, array $form, array $files,
		string $class_name, string $feature_name
	) : ?string
	{
		$object = reset($parameters);
		/** @noinspection PhpUnhandledExceptionInspection must call with a right $view class */
		$view_object = (is_object($object) && isA($object, $view))
			? reset($parameters)
			: Builder::create($view);
		return $view_object->$view_method_name(
			$parameters, $form, $files, $class_name, $feature_name
		);
	}

	//--------------------------------------------------------------------------------------- getView
	/**
	 * @param $view_name     string      the view name is the associated data class name
	 * @param $feature_names string[]    feature and inherited feature which view will be searched
	 * @param $template      string|null if a specific template is set, the view named with it will be
	 *                                   searched into the view / feature namespace first
	 * @return string[] callable
	 */
	private static function getView(string $view_name, array $feature_names, string $template = null)
		: array
	{
		$view_engine_name = get_class(View::current());
		$view_engine_name = Namespaces::shortClassName(Namespaces::of($view_engine_name));

		if (isset($template)) {
			$search = str_contains($template, '_')
				? Names::propertyToClass($template)
				: Names::methodToClass($template);
			foreach ([$view_engine_name . '_View', 'View'] as $suffix) {
				foreach ($feature_names as $feature_name) {
					[$class, $method] = Getter::get(
						$view_name, $feature_name, $search . '_' . $suffix
					);
					if (isset($class)) break 2;
				}
			}
		}

		if (!isset($class)) {
			foreach ([$view_engine_name . '_View', 'View'] as $suffix) {
				foreach ($feature_names as $feature_name) {
					[$class, $method] = $view_name
						? Getter::get($view_name, $feature_name, $suffix)
						: [null, null];
					if (isset($class)) break 2;
				}
			}
		}

		if (!isset($class)) {
			[$class, $method] = [__CLASS__ . BS . $view_engine_name . BS . 'Default_View', 'run'];
		}

		/** @noinspection PhpUndefinedVariableInspection if $class is set, then $method is set too */
		return [$class, $method];
	}

	//------------------------------------------------------------------------------------------ link
	/**
	 * Generates a link for to an object and feature, using parameters if needed
	 *
	 * @param $object     object|string|array|null linked object or class name
	 *                    Some internal calls may all this with [$class_name, $id]
	 * @param $feature    string|string[]|null linked feature name. Forced if in array
	 * @param $parameters string|string[]|object|object[]|null optional parameters list
	 * @param $arguments  string|string[]|null optional arguments list
	 * @return string
	 */
	public static function link(
		array|object|string|null $object,
		array|string             $feature    = null,
		array|object|string      $parameters = null,
		array|string             $arguments  = null
	) : string
	{
		return self::current()->link($object, $feature, $parameters, $arguments);
	}

	//-------------------------------------------------------------------------------------- redirect
	/**
	 * Generates a redirection link for to an object and feature, using parameters if needed
	 *
	 * @param $link    string a link generated by self::link()
	 * @param $options array|string Single or multiple options eg Target::MAIN
	 * @return string
	 */
	public static function redirect(string $link, array|string $options = []) : string
	{
		return self::current()->redirect($link, $options);
	}

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters   array   Parameters for the view. The first must be the context object.
	 * @param $form         array   Form parameters
	 * @param $files        array[] Files parameters
	 * @param $class_name   string  The context class name (class of the first parameter)
	 * @param $feature_name string  The feature class name
	 * @return ?string
	 */
	public static function run(
		array $parameters, array $form, array $files, string $class_name, string $feature_name
	) : ?string
	{
		$feature_names
			= (isset($parameters[Feature::FEATURE]) && ($parameters[Feature::FEATURE] !== $feature_name))
			? [$parameters[Feature::FEATURE], $feature_name]
			: [$feature_name];
		[$view_name, $view_method_name] = self::getView(
			$class_name, $feature_names, $parameters[Template::TEMPLATE] ?? null
		);
		return self::executeView(
			$view_name, $view_method_name, $parameters, $form, $files, $class_name, $feature_name
		);
	}

	//----------------------------------------------------------------------------------- setLocation
	/**
	 * Generate code for the current view to set the current location without redirecting to it
	 *
	 * @param $uri   string
	 * @param $title string
	 * @return string
	 */
	public static function setLocation(string $uri, string $title) : string
	{
		return self::current()->setLocation($uri, $title);
	}

}
