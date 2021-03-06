<?php
namespace ITRocks\Framework\Feature\List_;

/**
 * The list exception stops the current parsing of a search parameters of a field
 * and enabled parsing remaining fields
 *
 * TODO Support special messaging display for bad search part ?
 *
 * @assigned_feature Controller
 */
class Exception extends \Exception
{

	//----------------------------------------------------------------------------------- $expression
	/**
	 * Bad typed expression
	 *
	 * @var string
	 */
	protected string $expression;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $expression string
	 * @param $message string
	 */
	public function __construct(string $expression, string $message = '')
	{
		parent::__construct($message);
		$this->expression = $expression;
	}

	//--------------------------------------------------------------------------------- getExpression
	/**
	 * @return string
	 */
	public function getExpression() : string
	{
		return $this->expression;
	}

}
