<?php
namespace SAF\Tests;
use SAF\Framework\Aop;
use SAF\Framework\Component;

/**
 * @set Orders_Lines
 */
class Test_Order_Line
{
	use Component;

	//--------------------------------------------------------------------------------------- $client
	/**
	 * Delivery client for the line (for recursivity tests)
	 *
	 * @getter Aop::getObject
	 * @var Test_Client
	 */
	public $client;

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
	 * @getter Aop::getObject
	 * @mandatory
	 * @var Test_Order
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
