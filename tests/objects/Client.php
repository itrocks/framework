<?php
namespace ITRocks\Framework\Tests\Objects;

/**
 * A client class
 *
 * @representative number, name
 * @store_name test_clients
 */
class Client
{

	//--------------------------------------------------------------------------------------- $client
	/**
	 * Link to its own client (for recursivity tests)
	 *
	 * @link Object
	 * @var Client
	 */
	public $client;

	//----------------------------------------------------------------------------- $client_component
	/**
	 * @component
	 * @integrated simple
	 * @link Object
	 * @var Client_Component
	 */
	public $client_component;

	//----------------------------------------------------------------------------------------- $name
	/**
	 * Client full name
	 *
	 * @var string
	 */
	public $name;

	//--------------------------------------------------------------------------------------- $number
	/**
	 * Client number
	 *
	 * @var string
	 */
	public $number;

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return strval($this->number) . SP . strval($this->name);
	}

}
