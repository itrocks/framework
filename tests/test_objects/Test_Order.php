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
	 * @component
	 * @getter getCollection
	 * @mandatory
	 * @var Test_Order_Line[]
	 */
	private $lines;

	//------------------------------------------------------------------------------------- $salesmen
	/**
	 * Links to salesmen
	 *
	 * @getter getMap
	 * @var Test_Salesman[]
	 */
	private $salesmen;

}
