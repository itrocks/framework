<?php
namespace SAF\Tests;
use SAF\Framework\Component;

/**
 * An order line class
 *
 * @set Orders_Lines
 */
class Order_Line
{
	use Component;

	//--------------------------------------------------------------------------------------- $client
	/**
	 * Delivery client for the line (for recursivity tests)
	 *
	 * @link Object
	 * @var Client
	 */
	public $client;

	//----------------------------------------------------------------------------------------- $item
	/**
	 * Item
	 *
	 * @link Object
	 * @var Item
	 */
	public $item;

	//--------------------------------------------------------------------------------------- $number
	/**
	 * Line number
	 *
	 * @mandatory
	 * @var integer
	 */
	public $number;

	//---------------------------------------------------------------------------------------- $order
	/**
	 * Order
	 *
	 * @composite
	 * @mandatory
	 * @link Object
	 * @var Order
	 */
	public $order;

	//------------------------------------------------------------------------------------- $quantity
	/**
	 * Ordered quantity
	 *
	 * @mandatory
	 * @var float
	 */
	public $quantity;

}
