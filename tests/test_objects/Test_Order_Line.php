<?php
namespace SAF\Framework\Tests;
use SAF\Framework\Aop;
use SAF\Framework\Component;

/**
 * @set Test_Orders_Lines
 */
class Test_Order_Line implements Component
{

	//--------------------------------------------------------------------------------------- $client
	/**
	 * Delivery client for the line (for recursivity tests)
	 *
	 * @getter Aop::getObject
	 * @var Test_Client
	 */
	public $client;

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
	 * @getter Aop::getObject
	 * @mandatory
	 * @var Test_Order
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

	//--------------------------------------------------------------------------------------- dispose
	public function dispose()
	{
	}

	//------------------------------------------------------------------------------------- getParent
	public function getParent()
	{
		return $this->order;
	}

	//------------------------------------------------------------------------------------- setParent
	/**
	 * @param $order Test_Order
	 * @return \SAF\Framework\Component|Test_Order_Line
	 */
	public function setParent($order)
	{
		$this->order = $order;
		return $this;
	}

}
