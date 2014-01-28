<?php
namespace SAF\Framework;

use ReflectionParameter;

/**
 * A reflected function or method parameter
 */
class Reflection_Parameter extends ReflectionParameter
{

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
echo "parent string = [" . parent::__toString() . "]<br>";
		$default = $this->getDefaultValue();
		return ($this->isPassedByReference() ? '&' : '') . '$' . $this->name
		. ($this->isOptional()
			? (" = " . (is_numeric($default) ? $default : ("'" . $default. "'")))
			: ""
		);
	}

}
