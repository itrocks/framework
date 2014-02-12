<?php
namespace SAF\Framework;

use ReflectionParameter;

/**
 * A reflected function or method parameter
 */
class Reflection_Parameter extends ReflectionParameter
{

	//---------------------------------------------------------------------------- $internal_defaults
	/**
	 * @todo it may be hard, but should copy all internal functions default values here
	 * @var array
	 */
	private static $internal_defaults = array(
		'chop' => array('character_mask' => " \\t\\n\\r\\0\\x0B"),
		'ReflectionClass' => array(
			'export'                 => array('return' => false),
			'getMethods'             => array('filter' => 0),
			'getProperties'          => array('filter' => 0),
			'getStaticPropertyValue' => array('default' => null),
			'newInstanceArgs'        => array('args' => array())
		),
		'ReflectionFunction' => array(
			'export' => array('return' => false),
			'invoke' => array('args' => array())
		),
		'ReflectionParameter' => array(
			'export' => array('return' => false)
		),
		'ReflectionMethod' => array(
			'__construct' => array('name' => null),
			'export'      => array('return' => false)
		),
		'ReflectionProperty' => array(
			'export'   => array('return' => false),
			'getValue' => array('object' => null),
			'setValue' => array('value' => null)
		)
	);

	//------------------------------------------------------------------------------------ __toString
	/**
	 * Return the parameter as a PHP source string
	 *
	 * @example "$parameter_name"
	 * @example "&$parameter_name"
	 * @example "$parameter_name = 'default'"
	 * @example "$parameter_name = 10"
	 * @return string
	 */
	public function __toString()
	{
		$optional = $this->isOptional();
		if ($optional) {
			if ($this->isDefaultValueAvailable()) {
				$default = $this->getDefaultValue();
			}
			elseif ($this->getDeclaringClass()) {
				$default = isset(
					self::$internal_defaults
					[$this->getDeclaringClass()->name][$this->getDeclaringFunction()->name][$this->name]
				) ?
					self::$internal_defaults
					[$this->getDeclaringClass()->name][$this->getDeclaringFunction()->name][$this->name]
				: null;
			}
			elseif ($this->getDeclaringFunction()) {
				$default = isset(
					self::$internal_defaults[$this->getDeclaringFunction()->name][$this->name]
				) ? self::$internal_defaults[$this->getDeclaringFunction()->name][$this->name]
				: null;
			}
			else {
				$default = null;
			}
		}
		else {
			$default = null;
		}
		$default = is_string($default)
			? ('"' . str_replace('"', '\\"', $default) . '"')
			: var_export($default, true);
		return ($this->isPassedByReference() ? '&' : '') . '$' . $this->name
			. ($optional ? (' = ' . $default) : '');
	}

}
