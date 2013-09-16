<?php
namespace SAF\Framework;

/**
 * The configured plugins, as an element of an application configuration
 */
class Plugins implements Configurable
{
	use Current { current as private pCurrent; }

	public static $priorities = array(
		"top"     => 0,
		"highest" => 1,
		"higher"  => 2,
		"normal"  => 3,
		"lower"   => 4,
		"lowest"  => 5
	);

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $parameters array
	 */
	public function __construct($parameters = null)
	{
		$plugins = $this->parametersToPlugins($parameters);
		foreach ($plugins as $class_names) {
			foreach ($class_names as $class_name => $parameters) {
				if (is_numeric($class_name)) {
					$class_name = $parameters;
					$parameters = null;
				}
				$this->registerPlugin($class_name, $parameters);
			}
		}
	}

	//--------------------------------------------------------------------------------------- current
	/**
	 * @param $set_current Plugins
	 * @return Plugins
	 */
	public static function current(Plugins $set_current = null)
	{
		return self::pCurrent($set_current);
	}

	//--------------------------------------------------------------------------- parametersToPlugins
	/**
	 * Change parameters array to an ordered plugins list array
	 *
	 * @param $parameters array
	 * @return array
	 */
	private function parametersToPlugins($parameters)
	{
		foreach ($parameters as $priority => $classes) {
			$plugins[self::$priorities[$priority]] = $classes;
		}
		ksort($plugins);
		return $plugins;
	}

	//-------------------------------------------------------------------------------- registerPlugin
	/**
	 * @param $class_name string
	 * @param $parameters mixed[]|null
	 */
	public function registerPlugin($class_name, $parameters = null)
	{
		if (!is_subclass_of($class_name, 'SAF\Framework\Plugin')) {
			trigger_error($class_name . " is not an instance of Plugin", E_USER_ERROR);
		}
		else {
			call_user_func(array($class_name, "register"), $parameters);
		}
	}

}
