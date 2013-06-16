<?php
namespace SAF\Framework;

/**
 * Built-in SAF HTML view engine
 */
class Html_View_Engine implements Configurable, View_Engine
{

	//------------------------------------------------------------------------------------------ $css
	/**
	 * @var string
	 */
	private $css = "default";

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $parameters array
	 */
	public function __construct($parameters = null)
	{
		if (isset($parameters)) {
			foreach ($parameters as $key => $value) {
				$this->$key = $value;
			}
		}
	}

	//---------------------------------------------------------------------------------------- getCss
	/**
	 * @return string
	 */
	public function getCss()
	{
		return $this->css;
	}

	//-------------------------------------------------------------------------- getPossibleTemplates
	/**
	 * @param $class_name    string
	 * @param $feature_names string|string[]
	 * @return string[]
	 */
	public static function getPossibleTemplates($class_name, $feature_names)
	{
		if (!is_array($feature_names)) {
			$feature_names = array($feature_names);
		}
		foreach ($feature_names as $key => $feature_name) {
			if (!strpos($feature_name, ".")) {
				$feature_names[$key] .= ".html";
			}
		}
		$templates = array();
		$class_name = Namespaces::fullClassName($class_name);
		while ($class_name) {
			foreach ($feature_names as $feature_name) {
				$templates[] = Namespaces::shortClassName($class_name) . "_" . $feature_name;
			}
			$class_name = get_parent_class($class_name);
		}
		foreach ($feature_names as $feature_name) {
			$templates[] = "Default_$feature_name";
		}
		foreach ($feature_names as $feature_name) {
			$templates[] = $feature_name;
		}
		return $templates;
	}

	//------------------------------------------------------------------------------------------ link
	/**
	 * Generate a link for class name and parameters
	 *
	 * @param $object     object|string object or class name
	 * @param $parameters string|string[] string or array : parameters list (feature and other parameters)
	 * @return string
	 */
	public function link($object, $parameters = null)
	{
		$link = (is_object($object) && Dao::getObjectIdentifier($object))
			? (Namespaces::shortClassName(get_class($object)) . "/" . Dao::getObjectIdentifier($object))
			: Namespaces::shortClassName(is_object($object) ? get_class($object) : $object);
		if (isset($parameters)) {
			if (!is_array($parameters)) {
				$link .= "/" . $parameters;
			}
			else {
				foreach ($parameters as $key => $value) {
					if (!is_numeric($key)) {
						$link .= "/" . $value;
					}
					else {
						$link .= "/" . $key . "/" . $value;
					}
				}
			}
		}
		return "/" . $link;
	}

}
