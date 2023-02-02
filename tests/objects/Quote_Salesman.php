<?php
namespace ITRocks\Framework\Tests\Objects;

use ITRocks\Framework\Mapper;
use ITRocks\Framework\Reflection\Attribute\Class_\Store;

/**
 * A salesman with specific data for its link to a quote
 *
 * The 'link' annotation allow to consider this class as a link class
 *
 * @link Salesman
 */
#[Store('test_quote_salesman')]
class Quote_Salesman extends Salesman
{
	use Mapper\Component;

	//----------------------------------------------------------------------------------- $percentage
	public int $percentage;

	//---------------------------------------------------------------------------------------- $quote
	public ?Quote $quote;

	//------------------------------------------------------------------------------------- $salesman
	public ?Salesman $salesman;

}
