<?php
namespace SAF\Framework\AOP\Weaver;

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
