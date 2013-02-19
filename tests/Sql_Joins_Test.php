<?php
namespace SAF\Framework\Tests;
use SAF\Framework\Sql_Join;
use SAF\Framework\Sql_Joins;

class Sql_Joins_Test extends Unit_Test
{

	//------------------------------------------------------------------------------------ testSimple
	public function testSimple()
	{
		$this->assume(
			"simple properties (Test_Order::number)",
			Sql_Joins::newInstance('SAF\Framework\Tests\Test_Order')
				->addMultiple(array("date", "number"))
				->getJoins(),
			array("date" => null, "number" => null)
		);
	}

	//-------------------------------------------------------------------------------------- testJoin
	public function testJoin()
	{
		$this->assume(
			"simple join (Test_Order_Line::order.date)",
			Sql_Joins::newInstance('SAF\Framework\Tests\Test_Order_Line')
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
			"object property (Test_Order_Line::order)",
			Sql_Joins::newInstance('SAF\Framework\Tests\Test_Order_Line')
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
			"one-level collection property (Test_Order::lines.number)",
			Sql_Joins::newInstance('SAF\Framework\Tests\Test_Order')
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
			"multi-levels collection (Test_Order::client.number and Test_Order::client.client.number)",
			Sql_Joins::newInstance('SAF\Framework\Tests\Test_Order')
				->addMultiple(array("number", "client.number", "client.client.number", "client.name"))
				->getJoins(),
			array(
				"number" => null,
				"client" => Sql_Join::newInstance(
					Sql_Join::INNER, "t0", "id_client", "t1", "test_clients", "id"
				),
				"client.number" => null,
				"client.client" => Sql_Join::newInstance(
					Sql_Join::LEFT,  "t1", "id_client", "t2", "test_clients", "id"
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
			"one-level map property (Test_Order::salesmen.name)",
			$joins = Sql_Joins::newInstance('SAF\Framework\Tests\Test_Order')
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
			"reverse join (Test_Order::Test_Order_Line->order.number)",
			Sql_Joins::newInstance('SAF\Framework\Tests\Test_Order')
				->addMultiple(array(
					"date", "number", "Test_Order_Line->order.number", "Test_Order_Line->order.quantity"
				))
				->getJoins(),
			array(
				"date" => null,
				"number" => null,
				"Test_Order_Line->order" => Sql_Join::newInstance(
					Sql_Join::LEFT, "t0", "id", "t1", "orders_lines", "id_order"
				),
				"Test_Order_Line->order.number" => null,
				"Test_Order_Line->order.quantity" => null
			)
		);
		$this->assume(
			"reverse object (Test_Client::Test_Order_Line->client.order)",
			Sql_Joins::newInstance('SAF\Framework\Tests\Test_Client')
				->addMultiple(array("number", "name", "Test_Order_Line->client.order"))
				->getJoins(),
			array(
				"number" => null,
				"name" => null,
				"Test_Order_Line->client" => Sql_Join::newInstance(
					Sql_Join::LEFT, "t0", "id", "t1", "orders_lines", "id_client"
				),
				"Test_Order_Line->client.order" => Sql_Join::newInstance(
					Sql_Join::INNER, "t1", "id_order", "t2", "orders", "id", Sql_Join::OBJECT
				)
			)
		);
		$this->assume(
			"reverse map (Test_Salesman::Test_Order->salesmen.number)",
			Sql_Joins::newInstance('SAF\Framework\Tests\Test_Salesman')
				->addMultiple(array('SAF\Framework\Tests\Test_Order->salesmen.number', "name"))
				->getJoins(),
			array(
				'SAF\Framework\Tests\Test_Order->salesmen-link' => Sql_Join::newInstance(
					Sql_Join::LEFT, "t0", "id", "t1", "orders_salesmen_links", "id_salesman"
				),
				'SAF\Framework\Tests\Test_Order->salesmen' => Sql_Join::newInstance(
					Sql_Join::LEFT, "t1", "id_order", "t2", "orders", "id"
				),
				'SAF\Framework\Tests\Test_Order->salesmen.number' => null,
				"name" => null
			)
		);
	}

}
