<?php
namespace ITRocks\Framework\Tests\Objects;

use ITRocks\Framework\Mapper;

/**
 * A component class for @component @link Object properties testing
 *
 * @store_name test_client_components
 */
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
