<?php
namespace Framework\Tests;
use Framework\Sql_Join;
use Framework\Sql_Joins;

class Sql_Joins_Test extends Unit_Test
{

	//---------------------------------------------------------------------------- testCollectionJoin
	public function testCollectionJoin()
	{
		$this->assume(
			__METHOD__,
			Sql_Joins::newInstance("Test_Order")->add("date")->add("number")
				->add("lines.number")->add("lines.quantity")
				->getJoins(),
			array(
				"lines" => Sql_Join::newInstance(
					Sql_Join::INNER, "t0", "id", "t1", "orders_lines", "id_order"
				)
			)
		);
	}

	//------------------------------------------------------------------------------- testComplexJoin
	public function testComplexJoin()
	{
		$this->assume(
			__METHOD__,
			Sql_Joins::newInstance("Test_Order")->add("number")->add("client.number")
				->add("client.client.number")->add("client.name")
				->getJoins(),
			array(
				"client" => Sql_Join::newInstance(
					Sql_Join::INNER, "t0", "id_client", "t1", "test_clients", "id"
				),
				"client.client" => Sql_Join::newInstance(
					Sql_Join::LEFT,  "t1", "id_client", "t2", "test_clients", "id"
				),
			)
		);
	}

	//----------------------------------------------------------------------------- testComplexObject
	public function testComplexObject()
	{
		$this->assume(
			__METHOD__,
			Sql_Joins::newInstance("Test_Client")->add("number")->add("name")
				->add("Test_Order_Line->client.order")
				->getJoins(),
			array(
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
			Sql_Joins::newInstance("Test_Order_Line")->add("order.date")->add("order.number")
				->add("number")->add("quantity")
				->getJoins(),
			array("order" => Sql_Join::newInstance(
				Sql_Join::INNER, "t0", "id_order", "t1", "orders", "id"
			))
		);
	}

	//------------------------------------------------------------------------------------ testObject
	public function testObject()
	{
		$this->assume(
			__METHOD__,
			Sql_Joins::newInstance("Test_Order_Line")->add("number")->add("quantity")->add("order")
				->getJoins(),
			array("order" => Sql_Join::newInstance(
				Sql_Join::INNER, "t0", "id_order", "t1", "orders", "id", Sql_Join::OBJECT
			))
		);
	}

	//------------------------------------------------------------------------------- testReverseJoin
	public function testReverseJoin()
	{
		$this->assume(
			__METHOD__,
			Sql_Joins::newInstance("Test_Order")->add("date")->add("number")
				->add("Test_Order_Line->order.number")->add("Test_Order_Line->order.quantity")
				->getJoins(),
			array("Test_Order_Line->order" => Sql_Join::newInstance(
				Sql_Join::LEFT, "t0", "id", "t1", "orders_lines", "id_order"
			))
		);
	}

	//------------------------------------------------------------------------------------ testSimple
	public function testSimple()
	{
		$this->assume(
			__METHOD__,
			Sql_Joins::newInstance("Test_Order")->add("date")->add("number")->getJoins(),
			array()
		);
	}

}
