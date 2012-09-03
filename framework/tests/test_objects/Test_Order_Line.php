<?php

/**
 * @dataset orders_lines
 */
class Test_Order_Line
{

	/**
	 * @mandatory
	 * @var integer
	 */
	public $number;

	/**
	 * @mandatory
	 * @var float
	 */
	public $quantity;

	/**
	 * @mandatory
	 * @var Test_Order
	 */
	public $order;

	/**
	 * @var Test_Client
	 */
	public $client;

}

aop_add_before("read Test_Order_Line->order",  "Aop::objectGetter");
aop_add_before("read Test_Order_Line->client", "Aop::objectGetter");
