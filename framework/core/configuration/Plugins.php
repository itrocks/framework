<?php
namespace SAF\Framework;

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
	public function __construct($parameters = null)
	{
		$plugins = $this->parametersToPlugins($parameters);
		foreach ($plugins as $class_names) {
			foreach ($class_names as $class_name) {
				$this->registerPlugin(Namespaces::fullClassName($class_name));
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
	 */
	public function registerPlugin($class_name)
	{
		if (!is_subclass_of($class_name, __NAMESPACE__ . "\\Plugin")) {
			user_error($class_name . " is not an instance of Plugin");
		}
		else {
			call_user_func(array($class_name, "register"));
		}
	}

}
