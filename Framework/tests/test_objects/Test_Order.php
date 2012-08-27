<?php

/**
 * @dataset orders
 */
class Test_Order extends Test_Document
{

	/**
	 * @mandatory
	 * @var Test_Client
	 */
	private $client;

	/**
	 * @mandatory
	 * @foreign order
	 * @var Test_Order_Line[]
	 */
	private $lines;

}
