<?php
namespace SAF\Framework\Aop;

/**
 * Runkit Aop Handler
 */
class Runkit_Handler implements IHandler
{

	//---------------------------------------------------------------------------------------- $index
	/**
	 * @var integer
	 */
	public $index;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $index integer
	 */
	public function __construct($index)
	{
		$this->index = $index;
	}

}
