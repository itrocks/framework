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
	 * @component
	 * @getter getCollection
	 * @var Test_Order_Line[]
	 * @foreign order
	 */
	private $lines;

	//------------------------------------------------------------------------------------- $salesmen
	/**
	 * Links to salesmen
	 *
	 * @getter getMap
	 * @var Test_Salesman[]
	 * @foreign order
	 * @foreignlink salesman
	 */
	private $salesmen;

}
