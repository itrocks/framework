<?php
namespace SAF\Framework\Tests;
use SAF\Framework\Search_Object;
use SAF\Framework\Sql_Select_Builder;

class Sql_Select_Builder_Test extends Unit_Test
{

	//----------------------------------------------------------------------- testCollectionJoinQuery
	public function testCollectionJoinQuery()
	{
		$builder = new Sql_Select_Builder(
			'SAF\Framework\Tests\Test_Order',
			array("date", "number", "lines.number", "lines.quantity")
		);
		$this->assume(
			__METHOD__,
			$builder->buildQuery(),
			"SELECT t0.`date` AS `date`, t0.`number` AS `number`, t1.`number` AS `lines.number`, t1.`quantity` AS `lines.quantity` FROM `orders` t0 INNER JOIN `orders_lines` t1 ON t1.id_order = t0.id"
		);
	}

	//-------------------------------------------------------------------------- testComplexJoinQuery
	public function testComplexJoinQuery()
	{
		$builder = new Sql_Select_Builder(
			'SAF\Framework\Tests\Test_Order',
			array("number", "client.number", "client.client.number", "client.name")
		);
		$this->assume(
			__METHOD__,
			$builder->buildQuery(),
			"SELECT t0.`number` AS `number`, t1.`number` AS `client.number`, t2.`number` AS `client.client.number`, t1.`name` AS `client.name` FROM `orders` t0 INNER JOIN `test_clients` t1 ON t1.id = t0.id_client LEFT JOIN `test_clients` t2 ON t2.id = t1.id_client"
		);
	}

	//------------------------------------------------------------------------ testComplexObjectQuery
	public function testComplexObjectQuery()
	{
		$builder = new Sql_Select_Builder(
			'SAF\Framework\Tests\Test_Client',
			array("number", "name", "Test_Order_Line->client.order")
		);
		$this->assume(
			__METHOD__,
			$builder->buildQuery(),
			"SELECT t0.`number` AS `number`, t0.`name` AS `name`, t2.`date` AS `Test_Order_Line->client.order:date`, t2.`number` AS `Test_Order_Line->client.order:number`, t2.`id_client` AS `Test_Order_Line->client.order:client`, t2.id AS `Test_Order_Line->client.order:id` FROM `test_clients` t0 LEFT JOIN `orders_lines` t1 ON t1.id_client = t0.id INNER JOIN `orders` t2 ON t2.id = t1.id_order"
		);
	}

	//--------------------------------------------------------------------------------- testJoinQuery
	public function testJoinQuery()
	{
		$builder = new Sql_Select_Builder(
			'SAF\Framework\Tests\Test_Order_Line',
			array("order.date", "order.number", "number", "quantity")
		);
		$this->assume(
			__METHOD__,
			$builder->buildQuery(),
			"SELECT t1.`date` AS `order.date`, t1.`number` AS `order.number`, t0.`number` AS `number`, t0.`quantity` AS `quantity` FROM `orders_lines` t0 INNER JOIN `orders` t1 ON t1.id = t0.id_order"
		);
	}

	//--------------------------------------------------------------------------------- testLinkQuery
	public function testLinkQuery()
	{
		$builder = new Sql_Select_builder(
			'SAF\Framework\Tests\Test_Order',
			array("date", "number", "salesmen.name")
		);
		$this->assume(
			__METHOD__,
			$builder->buildQuery(),
			"SELECT t0.`date` AS `date`, t0.`number` AS `number`, t2.`name` AS `salesmen.name` FROM `orders` t0 LEFT JOIN `orders_salesmen_links` t1 ON t1.id_order = t0.id LEFT JOIN `salesmen` t2 ON t2.id = t1.id_salesman"
		);
	}

	//------------------------------------------------------------------------- testObjectObjectQuery
	public function testObjectQuery()
	{
		$builder = new Sql_Select_Builder(
			'SAF\Framework\Tests\Test_Order_Line',
			array("number", "quantity", "order")
		);
		$this->assume(
			__METHOD__,
			$builder->buildQuery(),
			"SELECT t0.`number` AS `number`, t0.`quantity` AS `quantity`, t1.`date` AS `order:date`, t1.`number` AS `order:number`, t1.`id_client` AS `order:client`, t1.id AS `order:id` FROM `orders_lines` t0 INNER JOIN `orders` t1 ON t1.id = t0.id_order"
		);
	}

	//-------------------------------------------------------------------------- testReverseJoinQuery
	public function testReverseJoinQuery()
	{
		$builder = new Sql_Select_Builder(
			'SAF\Framework\Tests\Test_Order',
			array("date", "number", "Test_Order_Line->order.number", "Test_Order_Line->order.quantity")
		);
		$this->assume(
			__METHOD__,
			$builder->buildQuery(),
			"SELECT t0.`date` AS `date`, t0.`number` AS `number`, t1.`number` AS `Test_Order_Line->order.number`, t1.`quantity` AS `Test_Order_Line->order.quantity` FROM `orders` t0 LEFT JOIN `orders_lines` t1 ON t1.id_order = t0.id"
		);
	}

	//------------------------------------------------------------------------------- testSimpleQuery
	public function testSimpleQuery()
	{
		$builder = new Sql_Select_Builder(
			'SAF\Framework\Tests\Test_Order',
			array("date", "number")
		);
		$this->assume(
			__METHOD__,
			$builder->buildQuery(),
			"SELECT t0.`date` AS `date`, t0.`number` AS `number` FROM `orders` t0"
		);
	}

	//------------------------------------------------------------------------- testWhereComplexQuery
	public function testWhereComplexQuery()
	{
		$client = Search_Object::newInstance('SAF\Framework\Tests\Test_Client');
		$client->number = 1;
		$builder = new Sql_Select_Builder(
			'SAF\Framework\Tests\Test_Order',
			array("date", "number", "lines"),
			array("OR" => array("lines.client.number" => $client->number, "number" => 2))
		);
		$this->assume(
			__METHOD__,
			$builder->buildQuery(),
			"SELECT t0.`date` AS `date`, t0.`number` AS `number`, t1.`id_client` AS `lines:client`, t1.`number` AS `lines:number`, t1.`id_order` AS `lines:order`, t1.`quantity` AS `lines:quantity`, t1.id AS `lines:id` FROM `orders` t0 INNER JOIN `orders_lines` t1 ON t1.id_order = t0.id LEFT JOIN `test_clients` t2 ON t2.id = t1.id_client WHERE (t2.`number` = 1 OR t0.`number` = 2)"
		);
	}

	//---------------------------------------------------------------------------- testWhereDeepQuery
	public function testWhereDeepQuery()
	{
		$builder = new Sql_Select_Builder(
			'SAF\Framework\Tests\Test_Order',
			array("date", "number"),
			array("number" => 1, "lines.number" => 2)
		);
		$this->assume(
			__METHOD__,
			$builder->buildQuery(),
			"SELECT t0.`date` AS `date`, t0.`number` AS `number` FROM `orders` t0 INNER JOIN `orders_lines` t1 ON t1.id_order = t0.id WHERE t0.`number` = 1 AND t1.`number` = 2"
		);
	}

	//-------------------------------------------------------------------------- testWhereObjectQuery
	public function testWhereObjectQuery()
	{
		/** @var $client Test_Client */
		$client = Search_Object::newInstance('SAF\Framework\Tests\Test_Client');
		$client->number = 1;
		$client->name = "Roger%";
		$properties = array("number", "name", "client");
		$builder = new Sql_Select_Builder('SAF\Framework\Tests\Test_Client', $properties, $client);
		$this->assume(
			__METHOD__,
			$builder->buildQuery(),
			"SELECT t0.`number` AS `number`, t0.`name` AS `name`, t1.`number` AS `client:number`, t1.`name` AS `client:name`, t1.`id_client` AS `client:client`, t1.id AS `client:id` FROM `test_clients` t0 LEFT JOIN `test_clients` t1 ON t1.id = t0.id_client WHERE t0.`number` = 1 AND t0.`name` LIKE \"Roger%\""
		);
	}

	//----------------------------------------------------------------------- testWhereSubObjectQuery
	public function testWhereSubObjectQuery()
	{
		$client = Search_Object::newInstance('SAF\Framework\Tests\Test_Client');
		$client->number = 1;
		$builder = new Sql_Select_Builder(
			'SAF\Framework\Tests\Test_Order',
			array("date", "number", "lines"),
			array("lines.client" => $client, "number" => 2)
		);
		$this->assume(
			__METHOD__,
			$builder->buildQuery(),
			"SELECT t0.`date` AS `date`, t0.`number` AS `number`, t1.`id_client` AS `lines:client`, t1.`number` AS `lines:number`, t1.`id_order` AS `lines:order`, t1.`quantity` AS `lines:quantity`, t1.id AS `lines:id` FROM `orders` t0 INNER JOIN `orders_lines` t1 ON t1.id_order = t0.id LEFT JOIN `test_clients` t2 ON t2.id = t1.id_client WHERE t2.`number` = 1 AND t0.`number` = 2"
		);
	}

	//-------------------------------------------------------------------------------- testWhereQuery
	public function testWhereQuery()
	{
		$builder = new Sql_Select_Builder(
			'SAF\Framework\Tests\Test_Order',
			array("date", "number"),
			array("number" => 1)
		);
		$this->assume(
			__METHOD__,
			$builder->buildQuery(),
			"SELECT t0.`date` AS `date`, t0.`number` AS `number` FROM `orders` t0 WHERE t0.`number` = 1"
		);
	}

	//--------------------------------------------------------------------- testWhereReverseJoinQuery
	public function testWhereReverseJoinQuery()
	{
		$builder = new Sql_Select_Builder(
			'SAF\Framework\Tests\Test_Order',
			array("date", "number", "Test_Order_Line->order.number", "Test_Order_Line->order.quantity"),
			array("Test_Order_Line->order.number" => "2")
		);
		$this->assume(
			__METHOD__,
			$builder->buildQuery(),
			"SELECT t0.`date` AS `date`, t0.`number` AS `number`, t1.`number` AS `Test_Order_Line->order.number`, t1.`quantity` AS `Test_Order_Line->order.quantity` FROM `orders` t0 LEFT JOIN `orders_lines` t1 ON t1.id_order = t0.id WHERE t1.`number` = 2"
		);
	}

}
