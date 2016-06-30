<?php
namespace SAF\Framework\Tests\Objects;

use SAF\Framework\Mapper\Component;

/**
 * A component class for @component @link Object properties testing
 */
class Client_Component
{
	use Component;

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
