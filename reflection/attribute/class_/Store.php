<?php
namespace ITRocks\Framework\Reflection\Attribute\Class_;

use Attribute;
use ITRocks\Framework\Reflection\Attribute\Always;
use ITRocks\Framework\Reflection\Attribute\Common;
use ITRocks\Framework\Reflection\Attribute\Inheritable;
use ITRocks\Framework\Reflection\Attribute\Template\Has_Get_Default_Arguments;
use ITRocks\Framework\Reflection\Attribute\Template\Has_Set_Declaring_Class;
use ITRocks\Framework\Reflection\Attribute\Template\Has_Set_Final;
use ITRocks\Framework\Reflection\Attribute\Template\Has_String_Value;
use ITRocks\Framework\Reflection\Interfaces\Reflection;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Attribute;
use ITRocks\Framework\Reflection\Reflection_Class_Common;
use ITRocks\Framework\Tools\Namespaces;

#[Always, Attribute(Attribute::TARGET_CLASS), Inheritable]
class Store implements Has_Get_Default_Arguments, Has_Set_Declaring_Class, Has_Set_Final
{
	use Common;
	use Has_String_Value;

	//------------------------------------------------------------------------------------- CALCULATE
	const CALCULATE = '¤calculate¤';

	//--------------------------------------------------------------------------------------- EXTENDS
	const EXTENDS = '¤extends¤';
	
	//----------------------------------------------------------------------------------- $calculated
	public bool $calculated = false;

	//---------------------------------------------------------------------------------------- $class
	public Reflection_Class|Reflection_Class_Common $class;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(bool|string $value = true)
	{
		$this->value = match($value) {
			true    => static::CALCULATE,
			false   => '',
			default => $value
		};
	}

	//--------------------------------------------------------------------------------- calculateName
	public function calculateName(Reflection_Class $class) : string
	{
		$name = strtolower(Namespaces::shortClassName(Set::of($class)->value));
		if ($class->isAbstract()) {
			$name .= '_view';
		}
		return $name;
	}

	//--------------------------------------------------------------------------------------- extends
	protected function extends(Reflection_Class $class) : void
	{
		foreach (Extend::of($class) as $extend_attribute) {
			foreach ($extend_attribute->extends as $extends) {
				$reflection_class = get_class($class);
				$store_extends    = static::of($reflection_class::of($extends));
				if ($value = $store_extends->value) {
					$this->calculated = $store_extends->calculated;
					$this->value      = $value;
					return;
				}
			}
		}
		$this->value = '';
	}

	//--------------------------------------------------------------------------- getDefaultArguments
	public static function getDefaultArguments(Reflection $reflection) : array
	{
		return [static::EXTENDS];
	}

	//----------------------------------------------------------------------------- setDeclaringClass
	public function setDeclaringClass(Reflection_Class $class) : void
	{
		$this->class = $class;
		if ($this->value === static::CALCULATE) {
			$this->calculated = true;
			$this->value      = $this->calculateName($class);
		}
	}

	//-------------------------------------------------------------------------------------- setFinal
	public function setFinal(Reflection|Reflection_Class $reflection) : void
	{
		if ($this->value === static::EXTENDS) {
			$this->extends($reflection);
		}
	}

	//------------------------------------------------------------------------------------- storeName
	public function storeName() : string
	{
		if ($this->value) {
			return $this->value;
		}
		$attributes = [];
		$this->class->mergeParentAttributes(
			$attributes, get_class($this), 0, $this->class, $this->class
		);
		if ($attributes) {
			/** @var $attribute Reflection_Attribute */
			$attribute = reset($attributes);
			/** @noinspection PhpUnhandledExceptionInspection static class */
			$store = $attribute->newInstance();
			return $store->storeName();
		}
		return $this->calculateName($this->class);
	}

}
