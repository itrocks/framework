<?php
namespace ITRocks\Framework\AOP\Weaver;

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
	/**
	 * @var integer
	 */
	public int $index;

	//------------------------------------------------------------------------------------ $joinpoint
	/**
	 * @var string|string[]
	 */
	public array|string $joinpoint;

	//----------------------------------------------------------------------------------------- $type
	/**
	 * @values self::const
	 * @var string
	 */
	public string $type;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $type      string
	 * @param $joinpoint string|string[]
	 * @param $index     integer
	 */
	public function __construct(string $type, array|string $joinpoint, int $index)
	{
		$this->index     = $index;
		$this->joinpoint = $joinpoint;
		$this->type      = $type;
	}

}
