<?php
namespace ITRocks\Framework\Tests\Objects;

use ITRocks\Framework\Mapper;
use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Reflection\Attribute\Property;

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
	 */
	public ?Client $client;

	//----------------------------------------------------------------------------------------- $item
	public ?Item $item;

	//--------------------------------------------------------------------------------------- $number
	public int $number = 1;

	//---------------------------------------------------------------------------------------- $order
	/**
	 * Order
	 *
	 * The #Composite attribute is not mandatory if it's guaranteed there will be only one property
	 * of type 'Order' into the class and its children.
	 */
	#[Property\Composite]
	public Order $order;

	//------------------------------------------------------------------------------------- $quantity
	public float $quantity = 1;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(int $number = null)
	{
		if (isset($number)) {
			$this->number = $number;
		}
	}

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		return $this->number . ' : ' . $this->item;
	}

}
