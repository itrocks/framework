<?php
namespace SAF\Framework\View\Html;

use SAF\Framework\Controller\Getter;
use SAF\Framework\Dao;
use SAF\Framework\Plugin\Configurable;
use SAF\Framework\Tools\Names;
use SAF\Framework\Tools\Namespaces;
use SAF\Framework;

/**
 * Built-in SAF HTML view engine
 */
class Engine implements Configurable, Framework\View\Engine
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

	//------------------------------------------------------------------------------- getTemplateFile
	/**
	 * @param $class_name    string   the associated data class name
	 * @param $feature_names string[] feature and inherited feature which view will be searched
	 * @param $template      string   if a specific template is set, the view named with it will be
	 *                       searched into the view / feature namespace first
	 * @return string the resulting path of the found template file
	 */
	public static function getTemplateFile($class_name, $feature_names, $template = null)
	{
		if (isset($template)) {
			foreach ($feature_names as $feature_name) {
				$class = Getter::get($class_name, $feature_name, $template, 'html', false)[0];
				if (isset($class)) break;
			}
		}

		if (!isset($class)) {
			foreach ($feature_names as $feature_name) {
				$class = Getter::get($class_name, $feature_name, '', 'html', false)[0];
				if (isset($class)) break;
			}
		}

		return Names::classToPath(isset($class) ? $class : $class_name . '_' . reset($feature_names))
			. '.html';
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
		$link = str_replace(BS, SL,
			(is_object($object) && Dao::getObjectIdentifier($object))
			? (get_class($object) . SL . Dao::getObjectIdentifier($object))
			: (is_object($object) ? get_class($object) : $object)
		);
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
