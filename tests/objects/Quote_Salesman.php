<?php
namespace ITRocks\Framework\Tests\Objects;

use ITRocks\Framework\Mapper;

/**
 * A salesman with specific data for its link to a quote
 *
 * The 'link' annotation allow to consider this class as a link class
 *
 * @link Salesman
 * @store_name test_quote_salesmen
 */
class Quote_Salesman extends Salesman
{
	use Mapper\Component;

	//----------------------------------------------------------------------------------- $percentage
	/**
	 * @var integer
	 */
	public int $percentage;

	//---------------------------------------------------------------------------------------- $quote
	/**
	 * @link Object
	 * @var ?Quote
	 */
	public ?Quote $quote;

	//------------------------------------------------------------------------------------- $salesman
	/**
	 * @link Object
	 * @var ?Salesman
	 */
	public ?Salesman $salesman;

}
