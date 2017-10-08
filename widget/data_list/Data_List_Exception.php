<?php
namespace ITRocks\Framework\Widget\Data_List;

use Exception;

/**
 * The Data_List exception stops the current parsing of a search parameters of a field
 * and enabled parsing remaining fields
 *
 * TODO Support special messaging display for bad search part ?
 */
class Data_List_Exception extends Exception
{

	//----------------------------------------------------------------------------------- $expression
	/**
	 * @var string
	 */
	protected $expression;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $expression string
	 * @param $message string
	 */
	public function __construct($expression, $message = '')
	{
		$this->expression = $expression;
		$this->message    = $message;
	}

	//--------------------------------------------------------------------------------- getExpression
	/**
	 * @return string
	 */
	public function getExpression()
	{
		return $this->expression;
	}

}
