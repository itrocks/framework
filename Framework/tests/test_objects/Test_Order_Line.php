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
	private $number;

	/**
	 * @mandatory
	 * @var float
	 */
	private $quantity;

	/**
	 * @mandatory
	 * @var Test_Order
	 */
	private $order;

	/**
	 * @var Test_Client
	 */
	private $client;
}
