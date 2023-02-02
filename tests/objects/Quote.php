<?php
namespace ITRocks\Framework\Tests\Objects;

use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Reflection\Attribute\Property;

/**
 * A quote class to test classes having the 'link' annotation
 */
#[Store('test_quotes')]
class Quote extends Document
{

	//--------------------------------------------------------------------------------------- $client
	public Client $client;

	//------------------------------------------------------------------------------------- $salesmen
	/**
	 * Links to salesmen, through a class having a one level 'link' annotation
	 *
	 * #Foreign order Optional, default would have been automatically calculated to 'quote'
	 * #Foreign_Link salesman Optional, default would have been automatically calculated to 'quote_salesman'
	 *
	 * @var Quote_Salesman[]
	 */
	#[Property\Component]
	public array $salesmen;

}
