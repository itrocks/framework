<?php
namespace ITRocks\Framework\Reflection\Attribute\Class_;

use Attribute;
use ITRocks\Framework\Reflection\Annotation\Class_\Extends_Annotation;
use ITRocks\Framework\Reflection\Attribute\Class_;
use ITRocks\Framework\Reflection\Attribute\Class_Has_Attributes;
use ITRocks\Framework\Reflection\Attribute\Has_String_Value;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Attribute;
use ITRocks\Framework\Tools\Namespaces;

#[Attribute]
class Store extends Class_
{
	use Has_String_Value;

	//------------------------------------------------------------------------------------- CALCULATE
	const CALCULATE = '¤calculate¤';

	//--------------------------------------------------------------------------------------- EXTENDS
	const EXTENDS = '¤extends¤';
	
	//----------------------------------------------------------------------------------- $calculated
	public bool $calculated = false;

	//---------------------------------------------------------------------------------------- $class
	public Reflection_Class|Class_Has_Attributes $class;

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
		foreach (Extends_Annotation::of($class)->values() as $extends) {
			$reflection_class = get_class($class);
			$store_extends    = static::of(new $reflection_class($extends));
			if ($value = $store_extends->value) {
				$this->calculated = $store_extends->calculated;
				$this->value      = $value;
				return;
			}
		}
		$this->value = '';
	}

	//--------------------------------------------------------------------------- getDefaultArguments
	public static function getDefaultArguments() : array
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
	public function setFinal(Reflection_Class $class) : void
	{
		if ($this->value === static::EXTENDS) {
			$this->extends($class);
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
		else {
			return $this->calculateName($this->class);
		}
	}

}
