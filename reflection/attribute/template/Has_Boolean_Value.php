<?php
namespace ITRocks\Framework\Reflection\Attribute\Template;

trait Has_Boolean_Value
{

	//---------------------------------------------------------------------------------------- $value
	public bool $value;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(bool $value = true)
	{
		$this->value = $value;
	}

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		return $this->value ? '1' : '0';
	}

	//--------------------------------------------------------------------------- getDefaultArguments
	public static function getDefaultArguments() : array
	{
		return [false];
	}

}
