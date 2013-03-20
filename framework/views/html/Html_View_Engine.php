<?php
namespace SAF\Framework;

class Html_View_Engine implements Configurable, View_Engine
{

	//------------------------------------------------------------------------------------------ $css
	/**
	 * @var string
	 */
	private $css = "default";

	//----------------------------------------------------------------------------------- __construct
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
	 * @param $class_name string
	 * @param $feature_name string
	 * @return string[]
	 */
	public static function getPossibleTemplates($class_name, $feature_name)
	{
		if (!strpos($feature_name, ".")) {
			$feature_name .= ".html";
		}
		$templates = array();
		$class_name = Namespaces::fullClassName($class_name);
		while ($class_name) {
			$templates[] = Namespaces::shortClassName($class_name) . "_" . $feature_name;
			$class_name = get_parent_class($class_name);
		}
		$templates[] = "Default_$feature_name";
		$templates[] = $feature_name;
		return $templates;
	}

	//------------------------------------------------------------------------------------------ link
	public function link($object, $parameters = null)
	{
		$link = is_object($object)
			? (Namespaces::shortClassName(get_class($object)) . "/" . Dao::getObjectIdentifier($object))
			: Namespaces::shortClassName($object);
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
