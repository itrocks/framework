<?php
namespace ITRocks\Framework\Sql\Builder;

use ITRocks\Framework\Mapper\Search_Object;
use ITRocks\Framework\Reflection\Annotation\Class_\Store_Name_Annotation;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Tests\Objects\Client;
use ITRocks\Framework\Tests\Objects\Item;
use ITRocks\Framework\Tests\Objects\Order;
use ITRocks\Framework\Tests\Objects\Order_Line;
use ITRocks\Framework\Tests\Objects\Quote;
use ITRocks\Framework\Tests\Objects\Quote_Salesman;
use ITRocks\Framework\Tests\Objects\Quote_Salesman_Additional;
use ITRocks\Framework\Tests\Objects\Salesman;
use ITRocks\Framework\Tests\Test;

/**
 * Sql select builder tests
 */
class Select_Test extends Test
{

	//----------------------------------------------------------------- testArrayImplicitOrWhereQuery
	public function testArrayImplicitOrWhereQuery() : void
	{
		$builder = new Select(
			Order::class,
			['date', 'number'],
			['number' => [1, 2]]
		);
		static::assertEquals(
			'SELECT t0.`date`, t0.`number`' . LF
			. 'FROM `test_orders` t0' . LF
			. 'WHERE (t0.`number` = "1" OR t0.`number` = "2")',
			$builder->buildQuery()
		);
	}

	//----------------------------------------------------------------------- testArrayWhereDeepQuery
	public function testArrayWhereDeepQuery() : void
	{
		$builder = new Select(
			Order::class,
			['date', 'number'],
			['number' => 1, 'lines' => [['number' => 2, 'item' => ['code' => 1]]]]
		);
		static::assertEquals(
			'SELECT t0.`date`, t0.`number`' . LF
			. 'FROM `test_orders` t0' . LF
			. 'INNER JOIN `test_order_lines` t1 ON t1.id_order = t0.id' . LF
			. 'LEFT JOIN `test_items` t2 ON t2.id = t1.id_item' . LF
			. 'WHERE t0.`number` = "1" AND t1.`number` = 2 AND t2.`code` = "1"',
			$builder->buildQuery()
		);
	}

	//----------------------------------------------------------------- testArrayWhereDeepQueryObject
	public function testArrayWhereDeepQueryObject() : void
	{
		$item       = new Item();
		$item->code = 1;
		$builder    = new Select(
			Order::class,
			['date', 'number'],
			['number' => 1, 'lines' => [['number' => 2, 'item' => $item]]]
		);
		static::assertEquals(
			'SELECT t0.`date`, t0.`number`' . LF
			. 'FROM `test_orders` t0' . LF
			. 'INNER JOIN `test_order_lines` t1 ON t1.id_order = t0.id' . LF
			. 'LEFT JOIN `test_items` t2 ON t2.id = t1.id_item' . LF
			. 'WHERE t0.`number` = "1" AND t1.`number` = 2 AND t2.`code` = "1"',
			$builder->buildQuery()
		);
	}

	//------------------------------------------------------------------ testArrayWhereDeepQueryShort
	public function testArrayWhereDeepQueryShort() : void
	{
		$builder = new Select(
			Order::class,
			['date', 'number'],
			['number' => 1, 'lines' => ['number' => 2, 'item' => ['code' => 1]]]
		);
		static::assertEquals(
			'SELECT t0.`date`, t0.`number`' . LF
			. 'FROM `test_orders` t0' . LF
			. 'INNER JOIN `test_order_lines` t1 ON t1.id_order = t0.id' . LF
			. 'LEFT JOIN `test_items` t2 ON t2.id = t1.id_item' . LF
			. 'WHERE t0.`number` = "1" AND t1.`number` = 2 AND t2.`code` = "1"',
			$builder->buildQuery()
		);
	}

	//---------------------------------------------------------------------- testArrayWhereDeepQuery2
	public function testArrayWhereDeepQuery2() : void
	{
		$builder = new Select(
			Order::class,
			['date', 'number'],
			[
				'number' => 1,
				'lines'  => [['number' => 2, 'item' => ['code' => 1, 'cross_selling' => [['code' => 3]]]]]
			]
		);
		static::assertEquals(
			'SELECT t0.`date`, t0.`number`' . LF
			. 'FROM `test_orders` t0' . LF
			. 'INNER JOIN `test_order_lines` t1 ON t1.id_order = t0.id' . LF
			. 'LEFT JOIN `test_items` t2 ON t2.id = t1.id_item' . LF
			. 'LEFT JOIN `test_items_items` t3 ON t3.id_item = t2.id' . LF
			. 'LEFT JOIN `test_items` t4 ON t4.id = t3.id_cross_selling' . LF
			. 'WHERE t0.`number` = "1" AND t1.`number` = 2 AND t2.`code` = "1" AND t4.`code` = "3"',
			$builder->buildQuery()
		);
	}

	//----------------------------------------------------------------- testArrayWhereDeepQuery2Short
	public function testArrayWhereDeepQuery2Short() : void
	{
		$builder = new Select(
			Order::class,
			['date', 'number'],
			[
				'number' => 1,
				'lines'  => ['number' => 2, 'item' => ['code' => 1, 'cross_selling' => ['code' => 3]]]
			]
		);
		static::assertEquals(
			'SELECT t0.`date`, t0.`number`' . LF
			. 'FROM `test_orders` t0' . LF
			. 'INNER JOIN `test_order_lines` t1 ON t1.id_order = t0.id' . LF
			. 'LEFT JOIN `test_items` t2 ON t2.id = t1.id_item' . LF
			. 'LEFT JOIN `test_items_items` t3 ON t3.id_item = t2.id' . LF
			. 'LEFT JOIN `test_items` t4 ON t4.id = t3.id_cross_selling' . LF
			. 'WHERE t0.`number` = "1" AND t1.`number` = 2 AND t2.`code` = "1" AND t4.`code` = "3"',
			$builder->buildQuery()
		);
	}

	//--------------------------------------------------------------------------- testArrayWhereQuery
	public function testArrayWhereQuery() : void
	{
		$builder = new Select(
			Order::class,
			['date', 'number'],
			['number' => 1, 'lines' => [['number' => 2]]]
		);
		static::assertEquals(
			'SELECT t0.`date`, t0.`number`' . LF
			. 'FROM `test_orders` t0' . LF
			. 'INNER JOIN `test_order_lines` t1 ON t1.id_order = t0.id' . LF
			. 'WHERE t0.`number` = "1" AND t1.`number` = 2',
			$builder->buildQuery()
		);
	}

	//------------------------------------------------------------------------ testArrayWhereWithNull
	public function testArrayWhereWithNull() : void
	{
		$builder = new Select(
			Order_Line::class,
			['number', 'quantity'],
			['client' => null]
		);
		static::assertEquals(
			'SELECT t0.`number`, t0.`quantity`' . LF
			. 'FROM `test_order_lines` t0' . LF
			. 'LEFT JOIN `test_clients` t1 ON t1.id = t0.id_client' . LF
			. 'WHERE t1.id IS NULL',
			$builder->buildQuery()
		);
	}

	//----------------------------------------------------------------------- testCollectionJoinQuery
	public function testCollectionJoinQuery() : void
	{
		$builder = new Select(
			Order::class,
			['date', 'number', 'lines.number', 'lines.quantity']
		);
		static::assertEquals(
			'SELECT t0.`date`, t0.`number`, t1.`number` AS `lines.number`, t1.`quantity` AS `lines.quantity`'
			. LF
			. 'FROM `test_orders` t0' . LF
			. 'INNER JOIN `test_order_lines` t1 ON t1.id_order = t0.id',
			$builder->buildQuery()
		);
	}

	//-------------------------------------------------------------------------- testComplexJoinQuery
	public function testComplexJoinQuery() : void
	{
		$builder = new Select(
			Order::class,
			['number', 'client.number', 'client.client.number', 'client.name']
		);
		static::assertEquals(
			'SELECT t0.`number`, t1.`number` AS `client.number`, t2.`number` AS `client.client.number`, t1.`name` AS `client.name`'
			. LF
			. 'FROM `test_orders` t0' . LF
			. 'INNER JOIN `test_clients` t1 ON t1.id = t0.id_client' . LF
			. 'LEFT JOIN `test_clients` t2 ON t2.id = t1.id_client',
			$builder->buildQuery()
		);
	}

	//------------------------------------------------------------------------ testComplexObjectQuery
	public function testComplexObjectQuery() : void
	{
		$builder = new Select(
			Client::class,
			['number', 'name', 'Order_Line(client).order']
		);
		static::assertEquals(
			'SELECT t0.`number`, t0.`name`,'
			. ' t2.`date` AS `Order_Line(client).order:date`,'
			. ' t2.`number` AS `Order_Line(client).order:number`,'
			. ' t2.id_client AS `Order_Line(client).order:client`,'
			. ' t2.id_delivery_client AS `Order_Line(client).order:delivery_client`,'
			. ' t2.`has_workflow` AS `Order_Line(client).order:has_workflow`,'
			. ' t2.id AS `Order_Line(client).order:id`' . LF
			. 'FROM `test_clients` t0' . LF
			. 'LEFT JOIN `test_order_lines` t1 ON t1.id_client = t0.id' . LF
			. 'LEFT JOIN `test_orders` t2 ON t2.id = t1.id_order',
			$builder->buildQuery()
		);
	}

	//--------------------------------------------------------------------------------- testJoinQuery
	public function testJoinQuery() : void
	{
		$builder = new Select(
			Order_Line::class,
			['order.date', 'order.number', 'number', 'quantity']
		);
		static::assertEquals(
			'SELECT t1.`date` AS `order.date`, t1.`number` AS `order.number`, t0.`number`, t0.`quantity`'
			. LF
			. 'FROM `test_order_lines` t0' . LF
			. 'INNER JOIN `test_orders` t1 ON t1.id = t0.id_order',
			$builder->buildQuery()
		);
	}

	//--------------------------------------------------------------------------------- testLinkQuery
	public function testLinkQuery() : void
	{
		$builder = new Select(
			Order::class,
			['date', 'number', 'salesmen.name']
		);
		static::assertEquals(
			'SELECT t0.`date`, t0.`number`, t2.`name` AS `salesmen.name`' . LF
			. 'FROM `test_orders` t0' . LF
			. 'LEFT JOIN `test_orders_salesmen` t1 ON t1.id_order = t0.id' . LF
			. 'LEFT JOIN `test_salesmen` t2 ON t2.id = t1.id_salesman',
			$builder->buildQuery()
		);
	}

	//------------------------------------------------------------------- testLinkedClassObjectSearch
	public function testLinkedClassObjectSearch() : void
	{
		// search text with internal ids to simulate a light salesman
		$search              = Search_Object::create(Quote_Salesman::class);
		$search->id_quote    = 101;
		$search->id_salesman = 102;
		$builder             = new Select(
			Quote_Salesman::class,
			['name', 'percentage'],
			$search
		);
		static::assertEquals(
			'SELECT t1.`name`, t0.`percentage`' . LF
			. 'FROM `test_quote_salesmen` t0' . LF
			. 'INNER JOIN `test_salesmen` t1 ON t1.id = t0.id_salesman' . LF
			. 'WHERE t0.id_quote = 101 AND t0.id_salesman = 102',
			$builder->buildQuery()
		);
		$search->quote        = Search_Object::create(Quote::class);
		$search->quote->id    = 101;
		$search->salesman     = Search_Object::create(Salesman::class);
		$search->salesman->id = 102;
		static::assertEquals(
			'SELECT t1.`name`, t0.`percentage`' . LF
			. 'FROM `test_quote_salesmen` t0' . LF
			. 'INNER JOIN `test_salesmen` t1 ON t1.id = t0.id_salesman' . LF
			. 'LEFT JOIN `test_quotes` t2 ON t2.id = t0.id_quote' . LF
			. 'LEFT JOIN `test_salesmen` t3 ON t3.id = t0.id_salesman' . LF
			. 'WHERE t2.id = 101 AND t3.id = 102',
			$builder->buildQuery()
		);
	}

	//-------------------------------------------------------------------------- testLinkedClassQuery
	public function testLinkedClassQuery() : void
	{
		$builder = new Select(
			Quote_Salesman::class,
			['name', 'percentage'],
			['name' => 'Robert', 'percentage' => 100]
		);
		static::assertEquals(
			'SELECT t1.`name`, t0.`percentage`' . LF
			. 'FROM `test_quote_salesmen` t0' . LF
			. 'INNER JOIN `test_salesmen` t1 ON t1.id = t0.id_salesman' . LF
			. 'WHERE t1.`name` = "Robert" AND t0.`percentage` = 100',
			$builder->buildQuery()
		);
	}

	//------------------------------------------------------------- testLinkedClassQueryWithTwoLevels
	public function testLinkedClassQueryWithTwoLevels() : void
	{
		$builder = new Select(
			Quote_Salesman_Additional::class,
			['name', 'percentage', 'additional_text'],
			['name' => 'Robert', 'percentage' => 100]
		);
		static::assertEquals(
			'SELECT t2.`name`, t1.`percentage`, t0.`additional_text`' . LF
			. 'FROM `test_quotes_salesmen_additional` t0' . LF
			. 'INNER JOIN `test_quote_salesmen` t1 ON t1.id = t0.id_salesman' . LF
			. 'INNER JOIN `test_salesmen` t2 ON t2.id = t1.id_salesman' . LF
			. 'WHERE t2.`name` = "Robert" AND t1.`percentage` = 100',
			$builder->buildQuery()
		);
	}

	//-------------------------------------------------------------------- testLinkedClassSelectQuery
	public function testLinkedClassSelectQuery() : void
	{
		$builder = new Select(
			Quote::class,
			['number', 'salesmen.name', 'salesmen.percentage']
		);
		static::assertEquals(
			'SELECT t0.`number`, t2.`name` AS `salesmen.name`, t1.`percentage` AS `salesmen.percentage`'
			. LF
			. 'FROM `test_quotes` t0' . LF
			. 'LEFT JOIN `test_quote_salesmen` t1 ON t1.id_quote = t0.id' . LF
			. 'LEFT JOIN `test_salesmen` t2 ON t2.id = t1.id_salesman',
			$builder->buildQuery()
		);
	}

	//------------------------------------------------------------------------------- testObjectQuery
	public function testObjectQuery() : void
	{
		$builder = new Select(
			Order_Line::class,
			['number', 'quantity', 'order']
		);
		static::assertEquals(
			'SELECT t0.`number`, t0.`quantity`,'
			. ' t1.`date` AS `order:date`,'
			. ' t1.`number` AS `order:number`,'
			. ' t1.id_client AS `order:client`,'
			. ' t1.id_delivery_client AS `order:delivery_client`,'
			. ' t1.`has_workflow` AS `order:has_workflow`,'
			. ' t1.id AS `order:id`' . LF
			. 'FROM `test_order_lines` t0' . LF
			. 'INNER JOIN `test_orders` t1 ON t1.id = t0.id_order',
			$builder->buildQuery()
		);
	}

	//-------------------------------------------------------------------- testQuoteSalesmanStoreName
	public function testQuoteSalesmanStoreName() : void
	{
		$store_name = Store_Name_Annotation::of(new Reflection_Class(Quote_Salesman::class))->value;
		static::assertEquals('test_quote_salesmen', $store_name, __METHOD__);
	}

	//-------------------------------------------------------------------------- testReverseJoinQuery
	public function testReverseJoinQuery() : void
	{
		$builder = new Select(
			Order::class,
			['date', 'number', 'Order_Line->order.number', 'Order_Line->order.quantity']
		);
		static::assertEquals(
			'SELECT t0.`date`, t0.`number`, t1.`number` AS `Order_Line->order.number`, t1.`quantity` AS `Order_Line->order.quantity`'
			. LF
			. 'FROM `test_orders` t0' . LF
			. 'LEFT JOIN `test_order_lines` t1 ON t1.id_order = t0.id',
			$builder->buildQuery()
		);
	}

	//------------------------------------------------------------------------------- testSimpleQuery
	public function testSimpleQuery() : void
	{
		$builder = new Select(
			Order::class,
			['date', 'number']
		);
		static::assertEquals(
			'SELECT t0.`date`, t0.`number`' . LF
			. 'FROM `test_orders` t0',
			$builder->buildQuery()
		);
	}

	//------------------------------------------------------------------------- testWhereComplexQuery
	public function testWhereComplexQuery() : void
	{
		$client         = Search_Object::create(Client::class);
		$client->number = 1;
		$builder        = new Select(
			Order::class,
			['date', 'number', 'lines'],
			['OR' => ['lines.client.number' => $client->number, 'number' => 2]]
		);
		static::assertEquals(
			'SELECT t0.`date`, t0.`number`, t1.id_client AS `lines:client`, t1.id_item AS `lines:item`, t1.`number` AS `lines:number`, t1.id_order AS `lines:order`, t1.`quantity` AS `lines:quantity`, t1.id AS `lines:id`'
			. LF
			. 'FROM `test_orders` t0' . LF
			. 'INNER JOIN `test_order_lines` t1 ON t1.id_order = t0.id' . LF
			. 'LEFT JOIN `test_clients` t2 ON t2.id = t1.id_client' . LF
			. 'WHERE (t2.`number` = "1" OR t0.`number` = "2")',
			$builder->buildQuery()
		);
	}

	//---------------------------------------------------------------------------- testWhereDeepQuery
	public function testWhereDeepQuery() : void
	{
		$builder = new Select(
			Order::class,
			['date', 'number'],
			['number' => 1, 'lines.number' => 2]
		);
		static::assertEquals(
			'SELECT t0.`date`, t0.`number`' . LF
			. 'FROM `test_orders` t0' . LF
			. 'INNER JOIN `test_order_lines` t1 ON t1.id_order = t0.id' . LF
			. 'WHERE t0.`number` = "1" AND t1.`number` = 2',
			$builder->buildQuery()
		);
	}

	//------------------------------------------------------------------ testWhereExistingObjectQuery
	public function testWhereExistingObjectQuery() : void
	{
		$client = new Client();
		/** @noinspection PhpUndefinedFieldInspection */
		$client->id = 12;
		$builder    = new Select(
			Order::class,
			['date', 'number', 'lines'],
			['lines.client' => $client, 'number' => 2]
		);
		static::assertEquals(
			'SELECT t0.`date`, t0.`number`, t1.id_client AS `lines:client`, t1.id_item AS `lines:item`, t1.`number` AS `lines:number`, t1.id_order AS `lines:order`, t1.`quantity` AS `lines:quantity`, t1.id AS `lines:id`'
			. LF
			. 'FROM `test_orders` t0' . LF
			. 'INNER JOIN `test_order_lines` t1 ON t1.id_order = t0.id' . LF
			. 'LEFT JOIN `test_clients` t2 ON t2.id = t1.id_client' . LF
			. 'WHERE t2.id = 12 AND t0.`number` = "2"',
			$builder->buildQuery()
		);
	}

	//-------------------------------------------------------------------------------- testWhereQuery
	public function testWhereQuery() : void
	{
		$builder = new Select(
			Order::class,
			['date', 'number'],
			['number' => 1]
		);
		static::assertEquals(
			'SELECT t0.`date`, t0.`number`' . LF
			. 'FROM `test_orders` t0' . LF
			. 'WHERE t0.`number` = "1"', $builder->buildQuery()
		);
	}

	//--------------------------------------------------------------------- testWhereReverseJoinQuery
	public function testWhereReverseJoinQuery() : void
	{
		$builder = new Select(
			Order::class,
			['date', 'number', 'Order_Line->order.number', 'Order_Line->order.quantity'],
			['Order_Line->order.number' => '2']
		);
		static::assertEquals(
			'SELECT t0.`date`, t0.`number`, t1.`number` AS `Order_Line->order.number`, t1.`quantity` AS `Order_Line->order.quantity`'
			. LF
			. 'FROM `test_orders` t0' . LF
			. 'LEFT JOIN `test_order_lines` t1 ON t1.id_order = t0.id' . LF
			. 'WHERE t1.`number` = 2',
			$builder->buildQuery()
		);
	}

	//-------------------------------------------------------------------- testWhereSearchObjectQuery
	public function testWhereSearchObjectQuery() : void
	{
		$client         = Search_Object::create(Client::class);
		$client->number = 1;
		$client->name   = 'Roger%';
		$properties     = ['number', 'name', 'client'];
		$builder        = new Select(Client::class, $properties, $client);
		static::assertEquals(
			'SELECT t0.`number`, t0.`name`, t1.id_client AS `client:client`, t1.`name` AS `client:name`, t1.`number` AS `client:number`, t1.id AS `client:id`'
			. LF
			. 'FROM `test_clients` t0' . LF
			. 'LEFT JOIN `test_clients` t1 ON t1.id = t0.id_client' . LF
			. 'WHERE t0.`name` LIKE "Roger%" AND t0.`number` = "1"',
			$builder->buildQuery()
		);
	}

	//----------------------------------------------------------------- testWhereSubSearchObjectQuery
	public function testWhereSubSearchObjectQuery() : void
	{
		$client         = Search_Object::create(Client::class);
		$client->number = 1;
		$builder        = new Select(
			Order::class,
			['date', 'number', 'lines'],
			['lines.client' => $client, 'number' => 2]
		);
		static::assertEquals(
			'SELECT t0.`date`, t0.`number`, t1.id_client AS `lines:client`, t1.id_item AS `lines:item`, t1.`number` AS `lines:number`, t1.id_order AS `lines:order`, t1.`quantity` AS `lines:quantity`, t1.id AS `lines:id`'
			. LF
			. 'FROM `test_orders` t0' . LF
			. 'INNER JOIN `test_order_lines` t1 ON t1.id_order = t0.id' . LF
			. 'LEFT JOIN `test_clients` t2 ON t2.id = t1.id_client' . LF
			. 'WHERE t2.`number` = "1" AND t0.`number` = "2"',
			$builder->buildQuery()
		);
	}

}
