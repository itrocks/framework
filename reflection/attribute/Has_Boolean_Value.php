<?php
namespace ITRocks\Framework\Reflection\Attribute;

trait Has_Boolean_Value
{
	
	//---------------------------------------------------------------------------------------- $value
	public ?bool $value = null;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(bool|null $value = true)
	{
		$this->value = $value;
	}

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		return isset($this->value) ? ($this->value ? '1' : '0') : '';
	}

	//--------------------------------------------------------------------------- getDefaultArguments
	public static function getDefaultArguments() : array
	{
		return [null];
	}

}
