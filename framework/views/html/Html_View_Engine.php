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
	 * @param array $parameters
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
	 * @param string $class_name
	 * @param string $feature_name
	 * @return multitype:string
	 */
	public static function getPossibleTemplates($class_name, $feature_name)
	{
		return array(
			$class_name . "_" . $feature_name . ".html",
			"Html_$feature_name.html"
		);
	}

	//------------------------------------------------------------------------------------------ link
	public function link($object, $parameters = null)
	{
		$link = is_object($object)
			? (Namespaces::shortClassName(get_class($object)) . "/" . Dao::getObjectIdentifier($object))
			: $object;
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
		return $link;
	}

}
