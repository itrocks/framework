<?php
namespace SAF\Framework\Tests;

class Test_Client
{

	//--------------------------------------------------------------------------------------- $number
	/**
	 * Client number
	 *
	 * @var string
	 */
	public $number;

	//----------------------------------------------------------------------------------------- $name
	/**
	 * Client full name
	 *
	 * @var string
	 */
	public $name;

	//--------------------------------------------------------------------------------------- $client
	/**
	 * Link to it's own client (for recursivity tests)
	 *
	 * @var Test_Client
	 */
	public $client; 

}
