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
	private
		/** @noinspection PhpUnusedPrivateFieldInspection */ $client;

	//---------------------------------------------------------------------------------------- $lines
	/**
	 * Lines
	 *
	 * @mandatory
	 * @var Test_Order_Line[]
	 * @foreign order
	 */
	private
		/** @noinspection PhpUnusedPrivateFieldInspection */ $lines;

	//------------------------------------------------------------------------------------- $salesmen
	/**
	 * Links to salesmen
	 *
	 * @var Test_Salesman[]
	 * @foreign order
	 * @foreignlink salesman
	 * @master
	 */
	/** @noinspection PhpUnusedPrivateFieldInspection */
	private
		/** @noinspection PhpUnusedPrivateFieldInspection */ $salesmen;

}
