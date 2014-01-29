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
		"chop" => array("character_mask" => " \\t\\n\\r\\0\\x0B")
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
			elseif ($this->getDeclaringFunction()) {
				$default = self::$internal_defaults[$this->getDeclaringFunction()->name][$this->name];
			}
			elseif ($this->getDeclaringClass()) {
				$default = self::$internal_defaults[$this->getDeclaringClass()->name][$this->name];
			}
			else {
				$default = null;
			}
		}
		else {
			$default = null;
		}
		if (!isset($default)) $default = "null";
		elseif (!is_numeric($default)) $default = '"' . str_replace('"', "\\\"", $default) . '"';
		return ($this->isPassedByReference() ? '&' : '') . '$' . $this->name
			. ($optional ? (" = " . $default) : "");
	}

}
