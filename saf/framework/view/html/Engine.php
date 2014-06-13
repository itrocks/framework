<?php
namespace SAF\Framework\View\Html;

use SAF\Framework\Builder;
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

		return isset($class)
			? (Names::classToPath($class) . '.html')
			: stream_resolve_include_path('default.html');
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
		// class name : not Built, not Set
		$class_names = is_string($object) ? $object : get_class($object);
		$class_name = Names::setToClass($class_names, false);
		$set_class = ($class_name != $class_names);
		while (Builder::isBuilt($class_name)) {
			$class_name = get_parent_class($class_name);
		}
		if ($set_class) {
			$class_name = Names::classToSet($class_name);
		}

		// build uri
		$link = str_replace(BS, SL,
			(is_object($object) && Dao::getObjectIdentifier($object))
			? ($class_name . SL . Dao::getObjectIdentifier($object))
			: $class_name
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

		// build arguments
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
