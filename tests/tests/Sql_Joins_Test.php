<?php
namespace SAF\Tests\Tests;
use SAF\Framework\Sql_Join;
use SAF\Framework\Sql_Joins;

class Sql_Joins_Test extends \SAF\Framework\Unit_Tests\Unit_Test
{

	//------------------------------------------------------------------------------------ testSimple
	public function testSimple()
	{
		$this->assume(
			"simple properties (Order::number)",
			Sql_Joins::newInstance('SAF\Tests\Order')
				->addMultiple(array("date", "number"))
				->getJoins(),
			array("date" => null, "number" => null)
		);
	}

	//-------------------------------------------------------------------------------------- testJoin
	public function testJoin()
	{
		$this->assume(
			"simple join (Order_Line::order.date)",
			Sql_Joins::newInstance('SAF\Tests\Order_Line')
				->addMultiple(array("order.date", "order.number", "number", "quantity"))
				->getJoins(),
			array(
				"order" => Sql_Join::newInstance(
					Sql_Join::INNER, "t0", "id_order", "t1", "orders", "id"
				),
				"order.date" => null,
				"order.number" => null,
				"number" => null,
				"quantity" => null
			)
		);
	}

	//------------------------------------------------------------------------------------ testObject
	public function testObject()
	{
		$this->assume(
			"object property (Order_Line::order)",
			Sql_Joins::newInstance('SAF\Tests\Order_Line')
				->addMultiple(array("number", "quantity", "order"))
				->getJoins(),
			array(
				"number" => null,
				"quantity" => null,
				"order" => Sql_Join::newInstance(
					Sql_Join::INNER, "t0", "id_order", "t1", "orders", "id", Sql_Join::OBJECT
				)
			)
		);
	}

	//-------------------------------------------------------------------------------- testCollection
	public function testCollection()
	{
		$this->assume(
			"one-level collection property (Order::lines.number)",
			Sql_Joins::newInstance('SAF\Tests\Order')
				->addMultiple(array("date", "number", "lines.number", "lines.quantity"))
				->getJoins(),
			array(
				"date" => null,
				"number" => null,
				"lines" => Sql_Join::newInstance(
					Sql_Join::INNER, "t0", "id", "t1", "orders_lines", "id_order"
				),
				"lines.number" => null,
				"lines.quantity" => null
			)
		);
		$this->assume(
			"multi-levels collection (Order::client.number and Order::client.client.number)",
			Sql_Joins::newInstance('SAF\Tests\Order')
				->addMultiple(array("number", "client.number", "client.client.number", "client.name"))
				->getJoins(),
			array(
				"number" => null,
				"client" => Sql_Join::newInstance(
					Sql_Join::INNER, "t0", "id_client", "t1", "clients", "id"
				),
				"client.number" => null,
				"client.client" => Sql_Join::newInstance(
					Sql_Join::LEFT,  "t1", "id_client", "t2", "clients", "id"
				),
				"client.client.number" => null,
				"client.name" => null
			)
		);
	}

	//--------------------------------------------------------------------------------------- testMap
	public function testMap()
	{
		$this->assume(
			"one-level map property (Order::salesmen.name)",
			$joins = Sql_Joins::newInstance('SAF\Tests\Order')
				->addMultiple(array("date", "number", "salesmen.name"))
				->getJoins(),
			array(
				"date" => null,
				"number" => null,
				"salesmen-link" => Sql_Join::newInstance(
					Sql_Join::LEFT, "t0", "id", "t1", "orders_salesmen_links", "id_order"
				),
				"salesmen" => Sql_Join::newInstance(
					Sql_Join::LEFT, "t1", "id_salesman", "t2", "salesmen", "id"
				),
				"salesmen.name" => null
			)
		);
	}

	//----------------------------------------------------------------------------------- testReverse
	public function testReverse()
	{
		$this->assume(
			"reverse join (Order::Order_Line->order.number)",
			Sql_Joins::newInstance('SAF\Tests\Order')
				->addMultiple(array(
					"date", "number", "Order_Line->order.number", "Order_Line->order.quantity"
				))
				->getJoins(),
			array(
				"date" => null,
				"number" => null,
				"Order_Line->order" => Sql_Join::newInstance(
					Sql_Join::LEFT, "t0", "id", "t1", "orders_lines", "id_order"
				),
				"Order_Line->order.number" => null,
				"Order_Line->order.quantity" => null
			)
		);
		$this->assume(
			"reverse object (Client::Order_Line->client.order)",
			Sql_Joins::newInstance('SAF\Tests\Client')
				->addMultiple(array("number", "name", "Order_Line->client.order"))
				->getJoins(),
			array(
				"number" => null,
				"name" => null,
				"Order_Line->client" => Sql_Join::newInstance(
					Sql_Join::LEFT, "t0", "id", "t1", "orders_lines", "id_client"
				),
				"Order_Line->client.order" => Sql_Join::newInstance(
					Sql_Join::INNER, "t1", "id_order", "t2", "orders", "id", Sql_Join::OBJECT
				)
			)
		);
		$this->assume(
			"reverse map (Salesman::Order->salesmen.number)",
			Sql_Joins::newInstance('SAF\Tests\Salesman')
				->addMultiple(array('SAF\Tests\Order->salesmen.number', "name"))
				->getJoins(),
			array(
				'SAF\Tests\Order->salesmen-link' => Sql_Join::newInstance(
					Sql_Join::LEFT, "t0", "id", "t1", "orders_salesmen_links", "id_salesman"
				),
				'SAF\Tests\Order->salesmen' => Sql_Join::newInstance(
					Sql_Join::LEFT, "t1", "id_order", "t2", "orders", "id"
				),
				'SAF\Tests\Order->salesmen.number' => null,
				"name" => null
			)
		);
	}

}
