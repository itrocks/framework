<?php
namespace SAF\Tests\Objects;

/**
 * An order class
 */
class Order extends Document
{

	//--------------------------------------------------------------------------------------- $client
	/**
	 * Client
	 *
	 * @mandatory
	 * @link Object
	 * @var Client
	 */
	private $client;

	//------------------------------------------------------------------------------ $delivery_client
	/**
	 * Delivery client
	 *
	 * @link Object
	 * @var Client
	 */
	private $delivery_client;

	//---------------------------------------------------------------------------------------- $lines
	/**
	 * Lines
	 *
	 * @link Collection
	 * @mandatory
	 * @var Order_Line[]
	 */
	private $lines;

	//------------------------------------------------------------------------------------- $salesmen
	/**
	 * Links to salesmen
	 *
	 * @link Map
	 * @var Salesman[]
	 * @(forgn) order Optional, default would have been automatically calculated to 'test_order'
	 * @(forgnlink) salesman Optional, default would have been automatically calculated to 'test_salesman'
	 */
	private $salesmen;

}
