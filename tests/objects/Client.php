<?php
namespace ITRocks\Framework\Tests\Objects;

use ITRocks\Framework\Reflection\Attribute\Class_\Representative;
use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Reflection\Attribute\Property;

/**
 * A client class
 */
#[Representative('number', 'name'), Store('test_clients')]
class Client
{

	//--------------------------------------------------------------------------------------- $client
	/** Link to its own client (for recursive tests) */
	public ?Client $client;

	//----------------------------------------------------------------------------- $client_component
	/** @integrated simple */
	#[Property\Component]
	public ?Client_Component $client_component;

	//----------------------------------------------------------------------------------------- $name
	/** Client full name */
	public string $name = '';

	//--------------------------------------------------------------------------------------- $number
	/** Client number */
	public string $number = '';

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		return $this->number . SP . $this->name;
	}

}
