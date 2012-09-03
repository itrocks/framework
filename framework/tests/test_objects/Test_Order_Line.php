<?php
namespace Framework\Tests;
use Framework\Aop;

/**
 * @dataset orders_lines
 */
class Test_Order_Line
{

	/**
	 * @var Test_Client
	 */
	public $client;

	/**
	 * @mandatory
	 * @var integer
	 */
	public $number;

	/**
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

Aop::registerObjectGetter("Framework\\Test\\Test_Order_Line->client");
Aop::registerObjectGetter("Framework\\Test\\Test_Order_Line->order");
