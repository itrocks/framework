<?php
namespace SAF\Tests;

/**
 * @set Orders
 */
class Test_Order extends Test_Document
{

	//--------------------------------------------------------------------------------------- $client
	/**
	 * Client
	 *
	 * @mandatory
	 * @var Test_Client
	 */
	private $client;

	//---------------------------------------------------------------------------------------- $lines
	/**
	 * Lines
	 *
	 * @getter getCollection
	 * @mandatory
	 * @var Test_Order_Line[]
	 */
	private $lines;

	//------------------------------------------------------------------------------------- $salesmen
	/**
	 * Links to salesmen
	 *
	 * @getter getMap
	 * @var Test_Salesman[]
	 * @foreign order Optional, default would have been automatically calculated to "test_order"
	 * @foreignlink salesman Optional, default would have been automatically calculated to "test_salesman"
	 */
	private $salesmen;

}
