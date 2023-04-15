<?php
namespace ITRocks\Framework\AOP\Weaver;

use ITRocks\Framework\Reflection\Attribute\Property\Values;

/**
 * The Aop handler
 */
class Handler implements IHandler
{

	//---------------------------------------------------------------------------------- $type values
	const AFTER  = 'after';
	const AROUND = 'around';
	const BEFORE = 'before';
	const READ   = 'read';
	const WRITE  = 'write';

	//---------------------------------------------------------------------------------------- $index
	public int $index;

	//------------------------------------------------------------------------------------ $joinpoint
	/** @var string|string[] */
	public array|string $joinpoint;

	//----------------------------------------------------------------------------------------- $type
	#[Values(self::class)]
	public string $type;

	//----------------------------------------------------------------------------------- __construct
	/** @param $joinpoint string|string[] */
	public function __construct(string $type, array|string $joinpoint, int $index)
	{
		$this->index     = $index;
		$this->joinpoint = $joinpoint;
		$this->type      = $type;
	}

}
