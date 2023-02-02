<?php
namespace ITRocks\Framework\Reflection\Attribute\Template;

trait Has_String_Value
{

	//---------------------------------------------------------------------------------------- $value
	public string $value;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(string $value = '')
	{
		$this->value = $value;
	}

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		return $this->value;
	}

}
