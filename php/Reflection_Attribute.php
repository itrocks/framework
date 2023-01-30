<?php
namespace ITRocks\Framework\PHP;

use ReflectionClass;
use ReflectionException;

class Reflection_Attribute
{

	//--------------------------------------------------------------------------------- IS_INSTANCEOF
	public const IS_INSTANCEOF = \ReflectionAttribute::IS_INSTANCEOF;

	//------------------------------------------------------------------------------------ $arguments
	private array $arguments;

	//----------------------------------------------------------------------------------------- $name
	private string $name;

	//------------------------------------------------------------------------------------- $repeated
	private bool $repeated = false;
	
	//--------------------------------------------------------------------------------------- $target
	private int $target;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(string $name, array $arguments = [], int $target = 0)
	{
		$this->arguments = $arguments;
		$this->name      = $name;
		$this->target    = $target;
	}

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

	//--------------------------------------------------------------------------------------- getName
	public function getName() : string
	{
		return $this->name;
	}

	//------------------------------------------------------------------------------------- getTarget
	public function getTarget() : int
	{
		return $this->target;
	}

	//------------------------------------------------------------------------------------ isRepeated
	public function isRepeated() : bool
	{
		return $this->repeated;
	}

	//----------------------------------------------------------------------------------- newInstance
	/**
	 * @throws ReflectionException
	 */
	public function newInstance() : object
	{
		return (new ReflectionClass($this->name))->newInstanceArgs($this->arguments);
	}

}
