<?php
namespace ITRocks\Framework\View\Html;

use ITRocks\Framework;
use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Getter;
use ITRocks\Framework\Controller\Target;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Plugin\Configurable;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\Tools\Paths;
use ITRocks\Framework\View\Html\Dom\Anchor;

/**
 * Built-in ITRocks HTML view engine
 */
class Engine implements Configurable, Framework\View\Engine
{

	//------------------------------------------- HTML view engine configuration array keys constants
	const CSS         = 'css';
	const CSS_DEFAULT = 'default';

	//------------------------------------------------------------------------------------------ $css
	/**
	 * @var string
	 */
	private string $css = self::CSS_DEFAULT;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $parameters array
	 */
	public function __construct(mixed $parameters = null)
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
	public function getCss() : string
	{
		return $this->css;
	}

	//------------------------------------------------------------------------------- getTemplateFile
	/**
	 * @param $class_name         string   the associated data class name
	 * @param $feature_names      string[] feature and inherited feature which view will be searched
	 * @param $template           string|null if a specific template is set, the view named with it
	 *                            will be searched into the view / feature namespace first
	 * @param $template_file_type string can search template files with another extension than 'html'
	 * @return string the resulting path of the found template file
	 */
	public static function getTemplateFile(
		string $class_name, array $feature_names, string $template = null,
		string $template_file_type = 'html'
	) : string
	{
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
	 * @param $object     array|object|string|null linked object or class name
	 *                    Some internal calls may all this with [$class_name, $id]
	 * @param $feature    string|string[]|null linked feature name, forced if in array
	 * @param $parameters string|string[]|object|object[]|null optional parameters list
	 * @param $arguments  string|string[]|null optional arguments list
	 * @return string
	 */
	public function link(
		array|object|string|null $object,
	  array|string             $feature    = null,
	  array|object|string      $parameters = null,
	  array|string             $arguments  = null
	) : string
	{
		// class name : not Built, not Set
		$class_names = is_object($object)
			? get_class($object)
			: (is_array($object) ? reset($object) : $object);
		$class_name = Names::setToClass($class_names, false);
		$set_class  = ($class_name !== $class_names);
		$class_name = Builder::current()->sourceClassName($class_name);
		if ($set_class) {
			$class_name = Names::classToSet($class_name);
		}

		// identifier
		$identifier = is_object($object)
			? Dao::getObjectIdentifier($object, 'id')
			: (is_array($object) ? end($object) : null);

		// Can simplify URI with removal of feature : only if there are no parameters
		if (!$parameters) {
			// change list URI to simple set-class URI (without the name of the feature)
			if (($feature === Feature::F_LIST) && !$identifier) {
				$class_name_test = $set_class ? Names::setToClass($class_name) : $class_name;
				$class_names     = $set_class ? $class_name : Names::classToSet($class_name);
				if ($class_name_test !== $class_names) {
					$class_name = $class_names;
					$feature    = null;
				}
			}
			// change output URI to simple URI (without the name of the feature)
			elseif (($feature === Feature::F_OUTPUT) && !$set_class && !is_string($object)) {
				$class_names = Names::classToSet($class_name);
				if ($class_name !== $class_names) {
					$feature = null;
				}
			}
		}

		// forced feature = can't change URI to simplified version : extract from array
		if (is_array($feature)) {
			$feature = reset($feature);
		}

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
						. SL . Dao::getObjectIdentifier($value, 'id');
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
					$link .= $key . '=' . urlencode($value ?? '');
				}
			}
		}

		return SL . $link;
	}

	//-------------------------------------------------------------------------------------- redirect
	/**
	 * Generates a redirection link for to an object and feature, using parameters if needed
	 *
	 * @param $link    array|string a link generated by self::link()
	 * @param $options array|string Single or multiple options eg Target::MAIN
	 * @return string
	 */
	public function redirect(array|string $link, array|string $options) : string
	{
		if (is_array($link)) {
			[$link, $data] = $link;
		}
		$link = str_starts_with($link, 'http')
			? str_replace('&amp;', '&', $link)
			: Paths::$uri_base . str_replace('&amp;', '&', $link);
		if (!is_array($options)) {
			$options = [$options];
		}
		$target = $options[Framework\View::TARGET] ?? false;
		if (
			isset($_GET['as_widget'])
			&& !str_contains($link, 'as_widget')
			&& (($target === false) || !in_array(substr($target, 0, 1), ['', '_'], true))
		) {
			$link .= (!str_contains($link, '?') ? '?' : '&') . 'as_widget';
		}
		$target = Target::RESPONSES;
		foreach ($options as $key => $option) {
			/** @noinspection PhpStrictComparisonWithOperandsOfDifferentTypesInspection inspector bug */
			if (
				($key === Framework\View::TARGET)
				|| (is_numeric($key) && str_starts_with($option, '#'))
			) {
				$target = $option;
			}
		}
		$element = new Anchor($link, $link);
		$element->addClass('auto-redirect');
		if (isset($data)) {
			$element->setData('post', http_build_query($data));
		}
		if ($target) {
			$element->setAttribute('target', $target);
		}
		return strval($element);
	}

	//----------------------------------------------------------------------------------- setLocation
	/**
	 * Generate code for the current view to set the current location without redirecting to it
	 *
	 * @param $uri   string
	 * @param $title string
	 * @return string
	 */
	public function setLocation(string $uri, string $title) : string
	{
		if ($title && str_contains($title, '<')) {
			$title = (new Template)->getHeadTitle($title);
		}
		if ($title && str_contains($title, '<')) {
			$title = trim(mParse($title, '>', '<'));
		}
		$uri = Paths::$uri_base . $uri;
		return "<script> window.history.pushState({reload: true}, '$title', '$uri'); </script>";
	}

}
