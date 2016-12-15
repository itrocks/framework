<?php
namespace ITRocks\Framework\View\Html;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller\Getter;
use ITRocks\Framework\Controller\Target;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Plugin\Configurable;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework;
use ITRocks\Framework\Tools\Paths;
use ITRocks\Framework\View\Html\Dom\Script;

/**
 * Built-in ITRocks HTML view engine
 */
class Engine implements Configurable, Framework\View\Engine
{

	//------------------------------------------- Html view engine configuration array keys constants
	const CSS = 'css';
	//----------------------------------------------------------------------------------- CSS_DEFAULT
	const CSS_DEFAULT = 'default';

	//------------------------------------------------------------------------------------------ $css
	/**
	 * @var string
	 */
	private $css = self::CSS_DEFAULT;

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
	 * @param $template_file_type string can search template files with another extension than 'html'
	 * @return string the resulting path of the found template file
	 */
	public static function getTemplateFile(
		$class_name, array $feature_names, $template = null, $template_file_type = 'html'
	) {
		if (isset($template)) {
			foreach ($feature_names as $feature_name) {
				$class = Getter::get($class_name, $feature_name, $template, $template_file_type, false)[0];
				if (isset($class)) break;
			}
		}

		if (!isset($class)) {
			foreach ($feature_names as $feature_name) {
				$class = Getter::get($class_name, $feature_name, '', $template_file_type, false)[0];
				if (isset($class)) break;
			}
		}

		return isset($class)
			? (Names::classToPath($class) . DOT . $template_file_type)
			: stream_resolve_include_path('default' . DOT . $template_file_type);
	}

	//------------------------------------------------------------------------------------------ link
	/**
	 * Generates a link for to an object and feature, using parameters if needed
	 *
	 * @param $object     object|string|array linked object or class name
	 *                    Some internal calls may all this with [$class_name, $id]
	 * @param $feature    string linked feature name
	 * @param $parameters string|string[]|object|object[] optional parameters list
	 * @param $arguments  string|string[] optional arguments list
	 * @return string
	 */
	public function link($object, $feature = null, $parameters = null, $arguments = null)
	{
		// class name : not Built, not Set
		$class_names = is_object($object)
			? get_class($object)
			: (is_array($object) ? reset($object) : $object);
		$class_name = Names::setToClass($class_names, false);
		$set_class  = ($class_name != $class_names);
		$class_name = Builder::current()->sourceClassName($class_name);
		if ($set_class) {
			$class_name = Names::classToSet($class_name);
		}

		// identifier
		$identifier = is_object($object)
			? Dao::getObjectIdentifier($object)
			: (is_array($object) ? end($object) : null);

		// build uri
		$link = str_replace(BS, SL, $identifier ? ($class_name . SL . $identifier) : $class_name);
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
					$link .= SL . Names::classToUri(get_class($value))
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

	//-------------------------------------------------------------------------------------- redirect
	/**
	 * Generates a redirection link for to an object and feature, using parameters if needed
	 *
	 * @param $link    string a link generated by self::link()
	 * @param $options array|string Single or multiple options eg Target::MAIN
	 * @return string
	 */
	public function redirect($link, $options)
	{
		$link = Paths::$uri_base . $link . (strpos('?', $link) ? '&' : '?') . 'as_widget';
		if (!is_array($options)) {
			$options = [$options];
		}
		$target = Target::MESSAGES;
		foreach ($options as $option) {
			if (substr($option, 0, 1) == '#') {
				$target = $option;
			}
		}
		$element = new Script(
			'$.get(' . Q . $link . Q .', function(data) {'
			. ' $(' . Q . $target . Q . ').html(data).build();'
			. ' });'
		);
		return strval($element);
	}

}
