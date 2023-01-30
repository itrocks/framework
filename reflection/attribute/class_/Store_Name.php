<?php
namespace ITRocks\Framework\Reflection\Attribute\Class_;

use Attribute;
use ITRocks\Framework\Reflection;
use ITRocks\Framework\Reflection\Attribute\Calculated;
use ITRocks\Framework\Reflection\Attribute\Has_Attributes;
use ITRocks\Framework\Reflection\Attribute\Inheritable;
use ITRocks\Framework\Reflection\Interfaces;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Class;
use ITRocks\Framework\Tools\Namespaces;

#[Attribute]
#[Inheritable]
class Store_Name implements Calculated
{
	use Reflection\Attribute;

	//----------------------------------------------------------------------------------- $calculated
	public bool $calculated = false;
	
	//---------------------------------------------------------------------------------------- $value
	public string $value;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(string $value = '')
	{
		$this->value = strtolower($value);
	}

	//------------------------------------------------------------------------------------- calculate
	public function calculate(Has_Attributes|Interfaces\Reflection $reflection) : void
	{
		if ($this->value) return;

		$this->calculated = true;
		$this->value = strtolower(Namespaces::shortClassName(Set::of($reflection)->value));
		/** @var $reflection Reflection_Class */
		if ($reflection->isAbstract()) {
			$this->value .= '_view';
		}

	}

}
