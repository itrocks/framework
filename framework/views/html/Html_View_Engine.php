<?php
namespace SAF\Framework;

class Html_View_Engine implements View_Engine
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
	public function __construct($parameters)
	{
		foreach ($parameters as $key => $value) {
			$this->$key = $value;
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
		$templates = array();
		$class_name = Namespaces::fullClassName($class_name);
		while ($class_name) {
			$templates[] = Namespaces::shortClassName($class_name) . "_" . $feature_name . ".html";
			$class_name = get_parent_class($class_name);
		}
		$templates[] = "Html_$feature_name.html";
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
