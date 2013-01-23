<?php
namespace SAF\Framework\Tests;

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
	 * @mandatory
	 * @var Test_Order_Line[]
	 * @foreign order
	 */
	private $lines;

	//------------------------------------------------------------------------------------- $salesmen
	/**
	 * Links to salesmen
	 *
	 * @var Test_Salesman[]
	 * @foreign order
	 * @foreignlink salesman
	 * @master
	 */
	private $salesmen;

}
