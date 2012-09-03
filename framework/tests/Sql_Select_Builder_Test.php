<?php
namespace Framework\Tests;
use \Framework\Sql_Builder;

class Sql_Select_Builder_Test extends Unit_Test
{

	//----------------------------------------------------------------------- testCollectionJoinQuery
	public function testCollectionJoinQuery()
	{
		$this->assume(
			__METHOD__,
			Sql_Builder::buildSelect(
				"Test_Order",
				array("date", "number", "lines.number", "lines.quantity")
			),
			"SELECT t0.`date` AS `date`, t0.`number` AS `number`, t1.`number` AS `lines.number`, t1.`quantity` AS `lines.quantity` FROM `orders` t0 INNER JOIN `orders_lines` t1 ON t1.id_order = t0.id"
		);
	}

	//-------------------------------------------------------------------------- testComplexJoinQuery
	public function testComplexJoinQuery()
	{
		$this->assume(
			__METHOD__,
			Sql_Builder::buildSelect(
				"Test_Order",
				array("number", "client.number", "client.client.number", "client.name")
			),
			"SELECT t0.`number` AS `number`, t1.`number` AS `client.number`, t2.`number` AS `client.client.number`, t1.`name` AS `client.name` FROM `orders` t0 INNER JOIN `test_clients` t1 ON t1.id = t0.id_client LEFT JOIN `test_clients` t2 ON t2.id = t1.id_client"
		);
	}

	//------------------------------------------------------------------------ testComplexObjectQuery
	public function testComplexObjectQuery()
	{
		$this->assume(
			__METHOD__,
			Sql_Builder::buildSelect(
				"Test_Client",
				array("number", "name", "Test_Order_Line->client.order")
			),
			"SELECT t0.`number` AS `number`, t0.`name` AS `name`, t2.`date` AS `Test_Order_Line->client.order:date`, t2.`number` AS `Test_Order_Line->client.order:number`, t2.`id_client` AS `Test_Order_Line->client.order:client` FROM `test_clients` t0 LEFT JOIN `orders_lines` t1 ON t1.id_client = t0.id INNER JOIN `orders` t2 ON t2.id = t1.id_order"
		);
	}

	//--------------------------------------------------------------------------------- testJoinQuery
	public function testJoinQuery()
	{
		$this->assume(
			__METHOD__,
			Sql_Builder::buildSelect(
				"Test_Order_Line",
				array("order.date", "order.number", "number", "quantity")
			),
			"SELECT t1.`date` AS `order.date`, t1.`number` AS `order.number`, t0.`number` AS `number`, t0.`quantity` AS `quantity` FROM `orders_lines` t0 INNER JOIN `orders` t1 ON t1.id = t0.id_order"
		);
	}

	//------------------------------------------------------------------------- testObjectObjectQuery
	public function testObjectQuery()
	{
		$this->assume(
			__METHOD__,
			Sql_Builder::buildSelect(
				"Test_Order_Line",
				array("number", "quantity", "order")
			),
			"SELECT t0.`number` AS `number`, t0.`quantity` AS `quantity`, t1.`date` AS `order:date`, t1.`number` AS `order:number`, t1.`id_client` AS `order:client` FROM `orders_lines` t0 INNER JOIN `orders` t1 ON t1.id = t0.id_order"
		);
	}

	//-------------------------------------------------------------------------- testReverseJoinQuery
	public function testReverseJoinQuery()
	{
		$this->assume(
			__METHOD__,
			Sql_Builder::buildSelect(
				"Test_Order",
				array("date", "number", "Test_Order_Line->order.number", "Test_Order_Line->order.quantity")
			),
			"SELECT t0.`date` AS `date`, t0.`number` AS `number`, t1.`number` AS `Test_Order_Line->order.number`, t1.`quantity` AS `Test_Order_Line->order.quantity` FROM `orders` t0 LEFT JOIN `orders_lines` t1 ON t1.id_order = t0.id"
		);
	}

	//------------------------------------------------------------------------------- testSimpleQuery
	public function testSimpleQuery()
	{
		$this->assume(
			__METHOD__,
			Sql_Builder::buildSelect(
				"Test_Order",
				array("date", "number")
			),
			"SELECT t0.`date` AS `date`, t0.`number` AS `number` FROM `orders` t0"
		);
	}

}
