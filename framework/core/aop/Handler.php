<?php
namespace SAF\AOP;

/**
 * The Aop handler
 */
class Handler implements IHandler
{

	//---------------------------------------------------------------------------------------- $index
	/**
	 * @var integer
	 */
	public $index;

	//------------------------------------------------------------------------------------ $joinpoint
	/**
	 * @var string[]
	 */
	public $joinpoint;

	//----------------------------------------------------------------------------------------- $type
	/**
	 * @values after, around, before, read, write
	 * @var string
	 */
	public $type;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $type      string
	 * @param $joinpoint string[]|string
	 * @param $index     integer
	 */
	public function __construct($type, $joinpoint, $index)
	{
		$this->index     = $index;
		$this->joinpoint = $joinpoint;
		$this->type      = $type;
	}

}
