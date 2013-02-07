<?php
namespace SAF\Framework\Tests;
use SAF\Framework\Aop;
use SAF\Framework\Component;

/**
 * @set Test_Orders_Lines
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
	 * @getter Aop::getObject
	 * @mandatory
	 * @parent
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
