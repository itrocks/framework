<?php
namespace SAF\Framework\Tests;

/**
 * @dataset orders
 */
class Test_Order extends Test_Document
{

	//--------------------------------------------------------------------------------------- $client
	/**
	 * Client
	 *
	 * @mandatory
	 * @var Test_Client
	 */
	private $client;

	//---------------------------------------------------------------------------------------- $lines
	/**
	 * Lines
	 *
	 * @contained
	 * @mandatory
	 * @var multitype:Test_Order_Line
	 * @foreign order
	 */
	private $lines;

}
