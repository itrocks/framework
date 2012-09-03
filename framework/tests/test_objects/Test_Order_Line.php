<?php

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

Aop::registerObjectGetter("Test_Order_Line->client");
Aop::registerObjectGetter("Test_Order_Line->order");
