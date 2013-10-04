<?php
namespace SAF\Tests;

use SAF\Framework\Component;

/**
 * A salesman with specific data for it's link to a quote
 *
 * The "link" annotation allow to consider this class as a link class
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
	private $salesman;

}
