<?php
namespace SAF\Framework\Tests\Objects;

/**
 * A client class
 *
 * @representative number, name
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
	public function __toString()
	{
		return strval($this->number) . SP . strval($this->name);
	}

}
