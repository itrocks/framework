<?php
namespace SAF\Framework\Aop;

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
	 * @param $joinpoint string[]
	 * @param $index     integer
	 * @param $type      string
	 */
	public function __construct($joinpoint, $index, $type)
	{
		$this->index     = $index;
		$this->joinpoint = $joinpoint;
		$this->type      = $type;
	}

}
