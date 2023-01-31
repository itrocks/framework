<?php
namespace ITRocks\Framework\Tests\Objects;

use ITRocks\Framework\Mapper;
use ITRocks\Framework\Reflection\Attribute\Class_\Store;

/**
 * An order line class
 */
#[Store('test_order_lines')]
class Order_Line
{
	use Mapper\Component;

	//--------------------------------------------------------------------------------------- $client
	/**
	 * Delivery client for the line (for recursive tests)
	 *
	 * @link Object
	 * @var ?Client
	 */
	public ?Client $client;

	//----------------------------------------------------------------------------------------- $item
	/**
	 * Item
	 *
	 * @link Object
	 * @var ?Item
	 */
	public ?Item $item;

	//--------------------------------------------------------------------------------------- $number
	/**
	 * Line number
	 *
	 * @mandatory
	 * @var integer
	 */
	public int $number = 1;

	//---------------------------------------------------------------------------------------- $order
	/**
	 * Order
	 *
	 * The 'composite' annotation is not mandatory if it's guaranteed there will be only one property
	 * of type 'Order' into the class and its children.
	 *
	 * @composite
	 * @link Object
	 * @var Order
	 */
	public Order $order;

	//------------------------------------------------------------------------------------- $quantity
	/**
	 * Ordered quantity
	 *
	 * @mandatory
	 * @var float
	 */
	public float $quantity = 1;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $number integer|null
	 */
	public function __construct(int $number = null)
	{
		if (isset($number)) {
			$this->number = $number;
		}
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return $this->number . ' : ' . $this->item;
	}

}
