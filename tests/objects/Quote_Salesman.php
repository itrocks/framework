<?php
namespace ITRocks\Framework\Tests\Objects;

use ITRocks\Framework\Mapper\Component;

/**
 * A salesman with specific data for its link to a quote
 *
 * The 'link' annotation allow to consider this class as a link class
 *
 * @link Salesman
 * @set Quotes_Salesmen
 */
class Quote_Salesman extends Salesman
{
	use Component;

	//----------------------------------------------------------------------------------- $percentage
	/**
	 * @var integer
	 */
	public $percentage;

	//---------------------------------------------------------------------------------------- $quote
	/**
	 * @link Object
	 * @var Quote
	 */
	public $quote;

	//------------------------------------------------------------------------------------- $salesman
	/**
	 * @link Object
	 * @var Salesman
	 */
	public $salesman;

}
