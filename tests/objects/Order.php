<?php /** @noinspection PhpUnusedPrivateFieldInspection */
namespace ITRocks\Framework\Tests\Objects;

/**
 * An order class
 *
 * @store_name test_orders
 */
class Order extends Document
{

	//--------------------------------------------------------------------------------------- $client
	/**
	 * Client
	 *
	 * @link Object
	 * @mandatory
	 * @var Client
	 */
	private Client $client;

	//------------------------------------------------------------------------------ $delivery_client
	/**
	 * Delivery client
	 *
	 * @link Object
	 * @var ?Client
	 */
	private ?Client $delivery_client;

	//---------------------------------------------------------------------------------------- $lines
	/**
	 * Lines
	 *
	 * @link Collection
	 * @mandatory
	 * @var Order_Line[]
	 */
	private array $lines;

	//------------------------------------------------------------------------------------- $salesmen
	/**
	 * Links to salesmen
	 *
	 * @(foreign) order Optional, default would have been automatically calculated to 'test_order'
	 * @(foreignlink) salesman Optional, default would have been automatically calculated to 'test_salesman'
	 * @link Map
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
