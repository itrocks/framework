<?php
namespace SAF\Framework\Tests;

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
	 * @var multitype:Test_Order_Line
	 */
	private $lines;

}
