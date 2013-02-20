<?php
namespace SAF\Tests;
use SAF\Framework\Aop;
use SAF\Framework\Component;

/**
 * @set Orders_Lines
 */
class Order_Line
{
	use Component;

	//--------------------------------------------------------------------------------------- $client
	/**
	 * Delivery client for the line (for recursivity tests)
	 *
	 * @link object
	 * @var Client
	 */
	public $client;

	//----------------------------------------------------------------------------------------- $item
	/**
	 * Item
	 *
	 * @link object
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
	 * @link object
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
