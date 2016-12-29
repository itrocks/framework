<?php
namespace ITRocks\Framework\Tests\Objects;

use ITRocks\Framework\Mapper;

/**
 * A component class for @component @link Object properties testing
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
	public $client;

	//---------------------------------------------------------------------------------- $little_name
	/**
	 * @var string
	 */
	public $little_name;

}
