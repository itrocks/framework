<?php
namespace ITRocks\Framework\Reflection\Attribute\Property;

use Attribute;
use ITRocks\Framework\Reflection\Attribute\Common;
use ITRocks\Framework\Reflection\Attribute\Inheritable;
use ITRocks\Framework\Reflection\Attribute\Template\Has_Set_Final;
use ITRocks\Framework\Reflection\Attribute\Template\Is_List;
use ITRocks\Framework\Reflection\Interfaces\Reflection;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Class;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;

/**
 * The values attribute lists the values the property can get.
 *
 * The program should not be able to give the property another value than one of the list.
 * This is useful for data controls on string[], float[] or integer[] properties.
 */
#[Attribute(Attribute::TARGET_PROPERTY), Inheritable]
class Values implements Has_Set_Final
{
	use Common;
	use Is_List;

	//----------------------------------------------------------------------- Special value constants
	const CONST = '::CONST';
	const LOCAL = '::LOCAL';

	//----------------------------------------------------------------------------- addClassConstants
	protected function addClassConstants(
		?string $class_name, string $mode, Reflection_Class $class
	) : void
	{
		if ($class_name) {
			$class = get_class($class);
			$class = new $class($class_name);
		}
		$constants = $class->getConstants(($mode === self::LOCAL) ? [] : [T_EXTENDS, T_USE]);
		foreach ($constants as $constant) {
			if (!is_array($constant)) {
				$this->values[] = $constant;
			}
		}
	}

	//-------------------------------------------------------------------------------------- setFinal
	public function setFinal(Reflection|Reflection_Property $reflection) : void
	{
		if (
			(count($this->values) === 1)
			&& class_exists(current($this->values))
		) {
			$this->addClassConstants(
				array_shift($this->values),
				self::CONST, $reflection->getDeclaringClass()
			);
			return;
		}
		$last_key   = null;
		$last_value = null;
		foreach ($this->values as $key => $value) {
			if (is_array($value)) {
				$this->values = array_merge($this->values, $value);
				unset($this->values[$key]);
			}
			elseif (
				in_array($value, [self::CONST, self::LOCAL])
				&& (is_null($last_value) || class_exists($last_value))
			) {
				if (isset($last_value)) {
					unset($this->values[$last_key]);
				}
				unset($this->values[$key]);
				$this->addClassConstants($last_value, $value, $reflection->getDeclaringClass());
			}
			$last_key   = $key;
			$last_value = $value;
		}
	}

}
