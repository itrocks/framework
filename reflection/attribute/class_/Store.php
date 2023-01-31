<?php
namespace ITRocks\Framework\Reflection\Attribute\Class_;

use Attribute;
use ITRocks\Framework\Reflection\Annotation\Class_\Extends_Annotation;
use ITRocks\Framework\Reflection\Attribute\Class_;
use ITRocks\Framework\Reflection\Attribute\Has_String_Value;
use ITRocks\Framework\Reflection\Interfaces\Reflection;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Class;
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
	public function calculateName() : string
	{
		$name = strtolower(Namespaces::shortClassName(Set::of($this->class)->value));
		if ($this->class->isAbstract()) {
			$name .= '_view';
		}
		return $name;
	}

	//--------------------------------------------------------------------------------------- extends
	protected function extends() : void
	{
		foreach (Extends_Annotation::of($this->class)->values() as $extends) {
			$reflection_class = get_class($this->class);
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

	//------------------------------------------------------------------------------------- setTarget
	public function setTarget(Reflection|Reflection_Class $target) : void
	{
		$this->class = $target;
		if (!in_array($this->value, [static::CALCULATE, static::EXTENDS])) return;

		if ($this->value === static::EXTENDS) {
			$this->extends();
		}
		else {
			$this->calculated = true;
			$this->value      = $this->calculateName();
		}
	}

}
