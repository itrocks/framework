<?php
namespace ITRocks\Framework\Tests\Objects;

use ITRocks\Framework\Reflection\Attribute\Class_\Store_Name;

/**
 * A quote class to test classes having the 'link' annotation
 */
#[Store_Name('test_quotes')]
class Quote extends Document
{

	//--------------------------------------------------------------------------------------- $client
	/**
	 * Client
	 *
	 * @link Object
	 * @mandatory
	 * @var Client
	 */
	public Client $client;

	//------------------------------------------------------------------------------------- $salesmen
	/**
	 * Links to salesmen, through a class having a one level 'link' annotation
	 *
	 * @(foreign) order Optional, default would have been automatically calculated to 'quote'
	 * @(foreignlink) salesman Optional, default would have been automatically calculated to 'quote_salesman'
	 * @link Collection
	 * @var Quote_Salesman[]
	 */
	public array $salesmen;

}
