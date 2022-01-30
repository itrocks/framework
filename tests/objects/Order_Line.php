<?php
namespace ITRocks\Framework\Tests\Objects;

use ITRocks\Framework\Mapper;

/**
 * An order line class
 *
 * @store_name test_order_lines
 */
class Order_Line
{
	use Mapper\Component;

	//--------------------------------------------------------------------------------------- $client
	/**
	 * Delivery client for the line (for recursivity tests)
	 *
	 * @link Object
	 * @var Client
	 */
	public $client;

	//----------------------------------------------------------------------------------------- $item
	/**
	 * Item
	 *
	 * @link Object
	 * @var Item
	 */
	public $item;

	//--------------------------------------------------------------------------------------- $number
	/**
	 * Line number
	 *
	 * @mandatory
	 * @var integer
	 */
	public $number;

	//---------------------------------------------------------------------------------------- $order
	/**
	 * Order
	 *
	 * The 'composite' annotation is not mandatory if it's guaranteed there will be only one property
	 * of type 'Order' into the class and its children.
	 *
	 * @composite
	 * @link Object
	 * @mandatory
	 * @var Order
	 */
	public $order;

	//------------------------------------------------------------------------------------- $quantity
	/**
	 * Ordered quantity
	 *
	 * @mandatory
	 * @var float
	 */
	public $quantity;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $number integer
	 */
	public function __construct($number = null)
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
		return strval($this->number) . ' : ' . $this->item;
	}

}
