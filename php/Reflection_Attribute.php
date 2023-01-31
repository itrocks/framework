<?php
namespace ITRocks\Framework\PHP;

use ITRocks\Framework\Reflection;

class Reflection_Attribute extends Reflection\Reflection_Attribute
{

	//------------------------------------------------------------------------------------ $arguments
	protected array $arguments = [];

	//----------------------------------------------------------------------------------------- $name
	protected string $name;

	//----------------------------------------------------------------------------------- addArgument
	public function addArgument(mixed $argument) : void
	{
		$this->arguments[] = $argument;
	}

	//---------------------------------------------------------------------------------- getArguments
	public function getArguments() : array
	{
		return $this->arguments;
	}

}
