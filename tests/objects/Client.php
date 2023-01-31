<?php
namespace ITRocks\Framework\Tests\Objects;

use ITRocks\Framework\Reflection\Attribute\Class_\Store;

/**
 * A client class
 *
 * @representative number, name
 */
#[Store('test_clients')]
class Client
{

	//--------------------------------------------------------------------------------------- $client
	/**
	 * Link to its own client (for recursive tests)
	 *
	 * @link Object
	 * @var ?Client
	 */
	public ?Client $client;

	//----------------------------------------------------------------------------- $client_component
	/**
	 * @component
	 * @integrated simple
	 * @link Object
	 * @var ?Client_Component
	 */
	public ?Client_Component $client_component;

	//----------------------------------------------------------------------------------------- $name
	/**
	 * Client full name
	 *
	 * @var string
	 */
	public string $name = '';

	//--------------------------------------------------------------------------------------- $number
	/**
	 * Client number
	 *
	 * @var string
	 */
	public string $number = '';

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return $this->number . SP . $this->name;
	}

}
