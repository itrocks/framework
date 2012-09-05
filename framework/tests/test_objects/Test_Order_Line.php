<?php
namespace SAF\Framework\Tests;
use SAF\Framework\Aop;

/**
 * @dataset orders_lines
 */
class Test_Order_Line
{

	/**
	 * @getter Aop::getObject
	 * @var Test_Client
	 */
	public $client;

	/**
	 * @mandatory
	 * @var integer
	 */
	public $number;

	/**
	 * @getter Aop::getObject
	 * @mandatory
	 * @var Test_Order
	 */
	public $order;

	/**
	 * @mandatory
	 * @var float
	 */
	public $quantity;

}
