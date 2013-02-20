<?php
namespace SAF\Tests;

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
	 * @link object
	 * @var Client
	 */
	public $client;

}
