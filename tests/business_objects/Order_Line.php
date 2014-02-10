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
	 * The "composite" annotation is not mandatory if it's guaranteed there will be only one property
	 * of type "Order" into the class and it's children.
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

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return strval($this->number) . " : " . $this->item;
	}

}
