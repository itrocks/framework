<?php
namespace SAF\Framework\Tests;
use SAF\Framework\Sql_Join;
use SAF\Framework\Sql_Joins;

class Sql_Joins_Test extends Unit_Test
{

	//---------------------------------------------------------------------------- testCollectionJoin
	public function testCollectionJoin()
	{
		$this->assume(
			__METHOD__,
			Sql_Joins::newInstance("Test_Order")
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
	}

	//------------------------------------------------------------------------------- testComplexJoin
	public function testComplexJoin()
	{
		$this->assume(
			__METHOD__,
			Sql_Joins::newInstance("Test_Order")
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

	//----------------------------------------------------------------------------- testComplexObject
	public function testComplexObject()
	{
		$this->assume(
			__METHOD__,
			Sql_Joins::newInstance("Test_Client")
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
	}

	//-------------------------------------------------------------------------------------- testJoin
	public function testJoin()
	{
		$this->assume(
			__METHOD__,
			Sql_Joins::newInstance("Test_Order_Line")
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

	//---------------------------------------------------------------------------------- testLinkJoin
	public function testLinkJoin()
	{
		$this->assume(
			__METHOD__,
			Sql_Joins::newInstance("Test_Order")
				->addMultiple(array("date", "number", "salesmen.name"))
				->getJoins(),
			array(
				"date" => null,
				"number" => null,
				"salesmen@link" => Sql_Join::newInstance(
					Sql_Join::LEFT, "t0", "id", "t1", "orders_salesmen_links", "id_order"
				),
				"salesmen" => Sql_Join::newInstance(
					Sql_Join::LEFT, "t1", "id_salesman", "t2", "salesmen", "id"
				),
				"salesmen.name" => null
			)
		);
	}

	//------------------------------------------------------------------------------------ testObject
	public function testObject()
	{
		$this->assume(
			__METHOD__,
			Sql_Joins::newInstance("Test_Order_Line")
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

	//------------------------------------------------------------------------------- testReverseJoin
	public function testReverseJoin()
	{
		$this->assume(
			__METHOD__,
			Sql_Joins::newInstance("Test_Order")
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
	}

	//------------------------------------------------------------------------------------ testSimple
	public function testSimple()
	{
		$this->assume(
			__METHOD__,
			Sql_Joins::newInstance("Test_Order")
				->addMultiple(array("date", "number"))
				->getJoins(),
			array("date" => null, "number" => null)
		);
	}

}
