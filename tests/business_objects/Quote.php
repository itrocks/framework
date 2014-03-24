<?php
namespace SAF\Tests;

/**
 * A quote class to test classes having the 'link' annotation
 */
class Quote extends Document
{

	//--------------------------------------------------------------------------------------- $client
	/**
	 * Client
	 *
	 * @mandatory
	 * @link Object
	 * @var Client
	 */
	public $client;

	//------------------------------------------------------------------------------------- $salesmen
	/**
	 * Links to salesmen, thru a class having a one level 'link' annotation
	 *
	 * @link Collection
	 * @var Quote_Salesman[]
	 * @(foreign) order Optional, default would have been automatically calculated to 'quote'
	 * @(foreignlink) salesman Optional, default would have been automatically calculated to 'quote_salesman'
	 */
	public $salesmen;

}
