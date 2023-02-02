<?php
namespace ITRocks\Framework\Tests\Objects;

use ITRocks\Framework\Mapper;
use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Reflection\Attribute\Property;

/**
 * A component class for #Component @link Object properties testing
 */
#[Store('test_client_components')]
class Client_Component
{
	use Mapper\Component;

	//--------------------------------------------------------------------------------------- $client
	#[Property\Composite]
	public Client $client;

	//---------------------------------------------------------------------------------- $little_name
	public string $little_name = '';

}
