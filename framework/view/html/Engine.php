<?php
namespace SAF\Framework\View\Html;

use SAF\Framework\Dao;
use SAF\Framework\Tools\Namespaces;
use SAF\Framework\View;
use SAF\Plugins;

/**
 * Built-in SAF HTML view engine
 */
class Engine implements Plugins\Configurable, View\Engine
{

	//------------------------------------------------------------------------------------------ $css
	/**
	 * @var string
	 */
	private $css = 'default';

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
			$feature_names = [$feature_names];
		}
		$templates = [];
		$class_name = Namespaces::fullClassName($class_name, false);
		while ($class_name) {
			foreach ($feature_names as $feature_name) {
				$templates[] = Namespaces::shortClassName($class_name) . '_' . $feature_name;
			}
			$class_name = get_parent_class($class_name);
		}
		foreach ($feature_names as $feature_name) {
			$templates[] = 'Default_' . $feature_name;
		}
		foreach ($feature_names as $feature_name) {
			$templates[] = $feature_name;
		}
		return $templates;
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
	public function link($object, $feature = null, $parameters = null, $arguments = null)
	{
		$link = (is_object($object) && Dao::getObjectIdentifier($object))
			? (Namespaces::shortClassName(get_class($object)) . SL . Dao::getObjectIdentifier($object))
			: Namespaces::shortClassName(is_object($object) ? get_class($object) : $object);
		if (isset($feature)) {
			$link .= SL . $feature;
		}
		if (isset($parameters)) {
			if (!is_array($parameters)) {
				$parameters = [$parameters];
			}
			foreach ($parameters as $key => $value) {
				if (!is_numeric($key)) {
					$link .= SL . $key;
				}
				if (is_object($value)) {
					$link .= SL . Namespaces::shortClassName(get_class($value))
						. SL . Dao::getObjectIdentifier($value);
				}
				else {
					$link .= SL . $value;
				}
			}
		}
		if (!empty($arguments)) {
			if (!is_array($arguments)) {
				$link .= '?' . urlencode($arguments);
			}
			else {
				$link .= '?';
				$first = true;
				foreach ($arguments as $key => $value) {
					if ($first) $first = false; else $link .= '&amp;';
					$link .= $key . '=' . urlencode($value);
				}
			}
		}
		return SL . $link;
	}

}
