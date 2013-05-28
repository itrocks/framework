<?php
namespace SAF\Tests;

/**
 * A client class
 */
class Client
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
	 * @link Object
	 * @var Client
	 */
	public $client;

}
