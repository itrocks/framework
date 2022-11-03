<?php
namespace ITRocks\Framework\Reflection;

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
	private static array $internal_defaults = [
		'chop' => ['character_mask' => " \\t\\n\\r\\0\\x0B"],
		'ReflectionClass' => [
			'export'                 => ['return' => false],
			'getMethods'             => ['filter' => 0],
			'getProperties'          => ['filter' => 0],
			'getStaticPropertyValue' => ['default' => null],
			'newInstanceArgs'        => ['args' => []]
		],
		'ReflectionFunction' => [
			'export' => ['return' => false],
			'invoke' => ['args' => []]
		],
		'ReflectionParameter' => [
			'export' => ['return' => false]
		],
		'ReflectionMethod' => [
			'__construct' => ['name' => null],
			'export'      => ['return' => false]
		],
		'ReflectionProperty' => [
			'export'   => ['return' => false],
			'getValue' => ['object' => null],
			'setValue' => ['value' => null]
		]
	];

	//------------------------------------------------------------------------------------ __toString
	/**
	 * Return the parameter as a PHP source string
	 *
	 * @example $parameter_name
	 * @example &$parameter_name
	 * @example $parameter_name = 'default'
	 * @example $parameter_name = 10
	 * @return string
	 */
	public function __toString() : string
	{
		$optional = $this->isOptional();
		if ($optional) {
			if ($this->isDefaultValueAvailable()) {
				$default = $this->getDefaultValue();
			}
			elseif ($this->getDeclaringClass()) {
				$default = self::$internal_defaults
					[$this->getDeclaringClass()->name][$this->getDeclaringFunction()->name][$this->name]
					?? null;
			}
			elseif ($this->getDeclaringFunction()) {
				$default = self::$internal_defaults[$this->getDeclaringFunction()->name][$this->name]
					?? null;
			}
			else {
				$default = null;
			}
		}
		else {
			$default = null;
		}
		$default = is_string($default)
			? (DQ . str_replace(DQ, BS . DQ, $default) . DQ)
			: var_export($default, true);
		return ($this->isPassedByReference() ? '&' : '') . '$' . $this->name
			. ($optional ? (' = ' . $default) : '');
	}

}
