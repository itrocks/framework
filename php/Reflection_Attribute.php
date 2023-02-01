<?php
namespace ITRocks\Framework\PHP;

use ITRocks\Framework\Reflection;
use ITRocks\Framework\Reflection\Interfaces;

class Reflection_Attribute extends Reflection\Reflection_Attribute
{

	//------------------------------------------------------------------------------------ $arguments
	/**
	 * Each argument is set as "raw php code", which will be evaluated on getArguments() call only
	 *
	 * @var string[]
	 */
	protected array $arguments = [];

	//----------------------------------------------------------------------------------------- $line
	public int $line = 0;

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
		return array_map(
			function($argument) { return eval('return ' . $argument . ';'); },
			$this->arguments
		);
	}

	//----------------------------------------------------------------------------- setFinalDeclaring
	public function setFinalDeclaring(
		Interfaces\Reflection $final, ?Interfaces\Reflection_Class $class
	) : void
	{
		$this->declaring_class = $class;
		$this->final           = $final;
	}

}
