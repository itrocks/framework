<?php /** @noinspection PhpUnusedPrivateFieldInspection */
namespace ITRocks\Framework\Tests\Objects;

use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Reflection\Attribute\Property;
use ITRocks\Framework\Reflection\Attribute\Property\Mandatory;

/**
 * An order class
 */
#[Store('test_orders')]
class Order extends Document
{

	//--------------------------------------------------------------------------------------- $client
	private Client $client;

	//------------------------------------------------------------------------------ $delivery_client
	private ?Client $delivery_client;

	//---------------------------------------------------------------------------------------- $lines
	/** @var Order_Line[] */
	#[Property\Component, Mandatory]
	private array $lines;

	//------------------------------------------------------------------------------------- $salesmen
	/**
	 * Links to salesmen
	 *
	 * #Foreign order Optional, default would have been automatically calculated to 'test_order'
	 * #Foreign_Link salesman Optional, default would have been automatically calculated to 'test_salesman'
	 *
	 * @var Salesman[]
	 */
	private array $salesmen;

	//-------------------------------------------------------------------------------------- addLines
	/**
	 * Lines are added and numeric keys recalculated
	 *
	 * @param $lines Order_Line[]
	 */
	public function addLines(array $lines) : void
	{
		$this->lines = array_merge($this->lines, $lines);
	}

	//-------------------------------------------------------------------------------------- setLines
	/**
	 * Lines (and keys) are replaced
	 *
	 * @param $lines Order_Line[]
	 */
	public function setLines(array $lines) : void
	{
		$this->lines = $lines;
	}

}
