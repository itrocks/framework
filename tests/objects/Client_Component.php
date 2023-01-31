<?php
namespace ITRocks\Framework\Tests\Objects;

use ITRocks\Framework\Mapper;
use ITRocks\Framework\Reflection\Attribute\Class_\Store;

/**
 * A component class for @component @link Object properties testing
 */
#[Store('test_client_components')]
class Client_Component
{
	use Mapper\Component;

	//--------------------------------------------------------------------------------------- $client
	/**
	 * @composite
	 * @link Object
	 * @var Client
	 */
	public Client $client;

	//---------------------------------------------------------------------------------- $little_name
	/**
	 * @var string
	 */
	public string $little_name = '';

}
