<?php
namespace SAF\Tests;

class Order extends Document
{

	//--------------------------------------------------------------------------------------- $client
	/**
	 * Client
	 *
	 * @mandatory
	 * @link object
	 * @var Client
	 */
	private $client;

	//------------------------------------------------------------------------------ $delivery_client
	/**
	 * Delivery client
	 *
	 * @link object
	 * @var Client
	 */
	private $delivery_client;

	//---------------------------------------------------------------------------------------- $lines
	/**
	 * Lines
	 *
	 * @link collection
	 * @mandatory
	 * @var Order_Line[]
	 */
	private $lines;

	//------------------------------------------------------------------------------------- $salesmen
	/**
	 * Links to salesmen
	 *
	 * @link map
	 * @var Salesman[]
	 * @(foreign) order Optional, default would have been automatically calculated to "test_order"
	 * @(foreignlink) salesman Optional, default would have been automatically calculated to "test_salesman"
	 */
	private $salesmen;

}
