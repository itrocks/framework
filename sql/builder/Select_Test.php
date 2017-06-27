<?php
namespace ITRocks\Framework\Sql\Builder;

use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Mapper\Search_Object;
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
	public function testArrayImplicitOrWhereQuery()
	{
		$builder = new Select(
			Order::class,
			['date', 'number'],
			['number' => [1, 2]]
		);
		$this->assume(
			__METHOD__,
			$builder->buildQuery(),
			'SELECT t0.`date`, t0.`number`' . LF
			. 'FROM `orders` t0' . LF
			. 'WHERE (t0.`number` = 1 OR t0.`number` = 2)'
		);
	}

	//--------------------------------------------------------------------------- testArrayWhereQuery
	public function testArrayWhereQuery()
	{
		$builder = new Select(
			Order::class,
			['date', 'number'],
			['number' => 1, 'lines' => [['number' => 2]]]
		);
		$this->assume(
			__METHOD__,
			$builder->buildQuery(),
			'SELECT t0.`date`, t0.`number`' . LF
			. 'FROM `orders` t0' . LF
			. 'INNER JOIN `orders_lines` t1 ON t1.id_order = t0.id' . LF
			. 'WHERE t0.`number` = 1 AND t1.`number` = 2'
		);
	}

	//----------------------------------------------------------------------- testArrayWhereDeepQuery
	public function testArrayWhereDeepQuery()
	{
		$builder = new Select(
			Order::class,
			['date', 'number'],
			['number' => 1, 'lines' => [['number' => 2, 'item' => ['code' => 1]]]]
		);
		$this->assume(
			__METHOD__,
			$builder->buildQuery(),
			'SELECT t0.`date`, t0.`number`' . LF
			. 'FROM `orders` t0' . LF
			. 'INNER JOIN `orders_lines` t1 ON t1.id_order = t0.id' . LF
			. 'LEFT JOIN `items` t2 ON t2.id = t1.id_item' . LF
			. 'WHERE t0.`number` = 1 AND t1.`number` = 2 AND t2.`code` = 1'
		);
	}

	//----------------------------------------------------------------- testArrayWhereDeepQueryObject
	public function testArrayWhereDeepQueryObject()
	{
		$item       = new Item();
		$item->code = 1;
		$builder    = new Select(
			Order::class,
			['date', 'number'],
			['number' => 1, 'lines' => [['number' => 2, 'item' => $item]]]
		);
		$this->assume(
			__METHOD__,
			$builder->buildQuery(),
			'SELECT t0.`date`, t0.`number`' . LF
			. 'FROM `orders` t0' . LF
			. 'INNER JOIN `orders_lines` t1 ON t1.id_order = t0.id' . LF
			. 'LEFT JOIN `items` t2 ON t2.id = t1.id_item' . LF
			. 'WHERE t0.`number` = 1 AND t1.`number` = 2 AND t2.`code` = 1'
		);
	}

	//------------------------------------------------------------------ testArrayWhereDeepQueryShort
	public function testArrayWhereDeepQueryShort()
	{
		$builder = new Select(
			Order::class,
			['date', 'number'],
			['number' => 1, 'lines' => ['number' => 2, 'item' => ['code' => 1]]]
		);
		$this->assume(
			__METHOD__,
			$builder->buildQuery(),
			'SELECT t0.`date`, t0.`number`' . LF
			. 'FROM `orders` t0' . LF
			. 'INNER JOIN `orders_lines` t1 ON t1.id_order = t0.id' . LF
			. 'LEFT JOIN `items` t2 ON t2.id = t1.id_item' . LF
			. 'WHERE t0.`number` = 1 AND t1.`number` = 2 AND t2.`code` = 1'
		);
	}

	//---------------------------------------------------------------------- testArrayWhereDeepQuery2
	public function testArrayWhereDeepQuery2()
	{
		$builder = new Select(
			Order::class,
			['date', 'number'],
			['number' => 1, 'lines' => [['number' => 2, 'item' => ['code' => 1, 'cross_selling' => [['code' => 3]]]]]]
		);
		$this->assume(
			__METHOD__,
			$builder->buildQuery(),
			'SELECT t0.`date`, t0.`number`' . LF
			. 'FROM `orders` t0' . LF
			. 'INNER JOIN `orders_lines` t1 ON t1.id_order = t0.id' . LF
			. 'LEFT JOIN `items` t2 ON t2.id = t1.id_item' . LF
			. 'LEFT JOIN `items_items` t3 ON t3.id_item = t2.id' . LF
			. 'LEFT JOIN `items` t4 ON t4.id = t3.id_cross_selling' . LF
			. 'WHERE t0.`number` = 1 AND t1.`number` = 2 AND t2.`code` = 1 AND t4.`code` = 3'
		);
	}

	//----------------------------------------------------------------- testArrayWhereDeepQuery2Short
	public function testArrayWhereDeepQuery2Short()
	{
		$builder = new Select(
			Order::class,
			['date', 'number'],
			['number' => 1, 'lines' => ['number' => 2, 'item' => ['code' => 1, 'cross_selling' => ['code' => 3]]]]
		);
		$this->assume(
			__METHOD__,
			$builder->buildQuery(),
			'SELECT t0.`date`, t0.`number`' . LF
			. 'FROM `orders` t0' . LF
			. 'INNER JOIN `orders_lines` t1 ON t1.id_order = t0.id' . LF
			. 'LEFT JOIN `items` t2 ON t2.id = t1.id_item' . LF
			. 'LEFT JOIN `items_items` t3 ON t3.id_item = t2.id' . LF
			. 'LEFT JOIN `items` t4 ON t4.id = t3.id_cross_selling' . LF
			. 'WHERE t0.`number` = 1 AND t1.`number` = 2 AND t2.`code` = 1 AND t4.`code` = 3'
		);
	}

	//------------------------------------------------------------------------ testArrayWhereWithNull
	public function testArrayWhereWithNull()
	{
		$builder = new Select(
			Order_Line::class,
			['number', 'quantity'],
			['client' => null]
		);
		$this->assume(
			__METHOD__,
			$builder->buildQuery(),
			'SELECT t0.`number`, t0.`quantity`' . LF
			. 'FROM `orders_lines` t0' . LF
			. 'LEFT JOIN `clients` t1 ON t1.id = t0.id_client' . LF
			. 'WHERE t1.`id` IS NULL'
		);
	}

	//----------------------------------------------------------------------- testCollectionJoinQuery
	public function testCollectionJoinQuery()
	{
		$builder = new Select(
			Order::class,
			['date', 'number', 'lines.number', 'lines.quantity']
		);
		$this->assume(
			__METHOD__,
			$builder->buildQuery(),
			'SELECT t0.`date`, t0.`number`, t1.`number` AS `lines.number`, t1.`quantity` AS `lines.quantity`' . LF
			. 'FROM `orders` t0' . LF . 'INNER JOIN `orders_lines` t1 ON t1.id_order = t0.id'
		);
	}

	//-------------------------------------------------------------------------- testComplexJoinQuery
	public function testComplexJoinQuery()
	{
		$builder = new Select(
			Order::class,
			['number', 'client.number', 'client.client.number', 'client.name']
		);
		$this->assume(
			__METHOD__,
			$builder->buildQuery(),
			'SELECT t0.`number`, t1.`number` AS `client.number`, t2.`number` AS `client.client.number`, t1.`name` AS `client.name`' . LF
			. 'FROM `orders` t0' . LF
			. 'INNER JOIN `clients` t1 ON t1.id = t0.id_client' . LF
			. 'LEFT JOIN `clients` t2 ON t2.id = t1.id_client'
		);
	}

	//------------------------------------------------------------------------ testComplexObjectQuery
	public function testComplexObjectQuery()
	{
		$builder = new Select(
			Client::class,
			['number', 'name', 'Order_Line->client.order']
		);
		$this->assume(
			__METHOD__,
			$builder->buildQuery(),
			'SELECT t0.`number`, t0.`name`,'
			. ' t2.`date` AS `Order_Line->client.order:date`,'
			. ' t2.`has_workflow` AS `Order_Line->client.order:has_workflow`,'
			. ' t2.`number` AS `Order_Line->client.order:number`,'
			. ' t2.`id_client` AS `Order_Line->client.order:client`,'
			. ' t2.`id_delivery_client` AS `Order_Line->client.order:delivery_client`,'
			. ' t2.id AS `Order_Line->client.order:id`' . LF
			. 'FROM `clients` t0' . LF
			. 'LEFT JOIN `orders_lines` t1 ON t1.id_client = t0.id' . LF
			. 'LEFT JOIN `orders` t2 ON t2.id = t1.id_order'
		);
	}

	//--------------------------------------------------------------------------------- testJoinQuery
	public function testJoinQuery()
	{
		$builder = new Select(
			Order_Line::class,
			['order.date', 'order.number', 'number', 'quantity']
		);
		$this->assume(
			__METHOD__,
			$builder->buildQuery(),
			'SELECT t1.`date` AS `order.date`, t1.`number` AS `order.number`, t0.`number`, t0.`quantity`' . LF
			. 'FROM `orders_lines` t0' . LF
			. 'INNER JOIN `orders` t1 ON t1.id = t0.id_order'
		);
	}

	//-------------------------------------------------------------------------- testLinkedClassQuery
	public function testLinkedClassQuery()
	{
		$builder = new Select(
			Quote_Salesman::class,
			['name', 'percentage'],
			['name' => 'Robert', 'percentage' => 100]
		);
		$this->assume(
			__METHOD__,
			$builder->buildQuery(),
			'SELECT t1.`name`, t0.`percentage`' . LF
			. 'FROM `quotes_salesmen` t0' . LF
			. 'INNER JOIN `test_salesmen` t1 ON t1.id = t0.id_salesman' . LF
			. 'WHERE t1.`name` = "Robert" AND t0.`percentage` = 100'
		);
	}

	//------------------------------------------------------------------- testLinkedClassObjectSearch
	public function testLinkedClassObjectSearch()
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
		$this->assume(
			__METHOD__ . '.short',
			$builder->buildQuery(),
			'SELECT t1.`name`, t0.`percentage`' . LF
			. 'FROM `quotes_salesmen` t0' . LF
			. 'INNER JOIN `test_salesmen` t1 ON t1.id = t0.id_salesman' . LF
			. 'WHERE t0.id_quote = 101 AND t0.id_salesman = 102'
		);
		$search->quote        = Search_Object::create(Quote::class);
		$search->quote->id    = 101;
		$search->salesman     = Search_Object::create(Salesman::class);
		$search->salesman->id = 102;
		$this->assume(
			__METHOD__ . '.long',
			$builder->buildQuery(),
			'SELECT t1.`name`, t0.`percentage`' . LF
			. 'FROM `quotes_salesmen` t0' . LF
			. 'INNER JOIN `test_salesmen` t1 ON t1.id = t0.id_salesman' . LF
			. 'LEFT JOIN `quotes` t2 ON t2.id = t0.id_quote' . LF
			. 'LEFT JOIN `test_salesmen` t3 ON t3.id = t0.id_salesman' . LF
			. 'WHERE t2.`id` = 101 AND t3.`id` = 102'
		);
	}

	//------------------------------------------------------------- testLinkedClassQueryWithTwoLevels
	public function testLinkedClassQueryWithTwoLevels()
	{
		$builder = new Select(
			Quote_Salesman_Additional::class,
			['name', 'percentage', 'additional_text'],
			['name' => 'Robert', 'percentage' => 100]
		);
		$this->assume(
			__METHOD__,
			$builder->buildQuery(),
			'SELECT t2.`name`, t1.`percentage`, t0.`additional_text`' . LF
			. 'FROM `quotes_salesmen_additional` t0' . LF
			. 'INNER JOIN `quotes_salesmen` t1 ON t1.id = t0.id_salesman' . LF
			. 'INNER JOIN `test_salesmen` t2 ON t2.id = t1.id_salesman' . LF
			. 'WHERE t2.`name` = "Robert" AND t1.`percentage` = 100'
		);
	}

	//-------------------------------------------------------------------- testLinkedClassSelectQuery
	public function testLinkedClassSelectQuery()
	{
		$builder = new Select(
			Quote::class,
			['number', 'salesmen.name', 'salesmen.percentage']
		);
		$this->assume(
			__METHOD__,
			$builder->buildQuery(),
			'SELECT t0.`number`, t2.`name` AS `salesmen.name`, t1.`percentage` AS `salesmen.percentage`' . LF
			. 'FROM `quotes` t0' . LF
			. 'LEFT JOIN `quotes_salesmen` t1 ON t1.id_quote = t0.id' . LF
			. 'LEFT JOIN `test_salesmen` t2 ON t2.id = t1.id_salesman'
		);
	}

	//--------------------------------------------------------------------------------- testLinkQuery
	public function testLinkQuery()
	{
		$builder = new Select(
			Order::class,
			['date', 'number', 'salesmen.name']
		);
		$this->assume(
			__METHOD__,
			$builder->buildQuery(),
			'SELECT t0.`date`, t0.`number`, t2.`name` AS `salesmen.name`' . LF
			. 'FROM `orders` t0' . LF
			. 'LEFT JOIN `orders_test_salesmen` t1 ON t1.id_order = t0.id' . LF
			. 'LEFT JOIN `test_salesmen` t2 ON t2.id = t1.id_salesman'
		);
	}

	//------------------------------------------------------------------------------- testObjectQuery
	public function testObjectQuery()
	{
		$builder = new Select(
			Order_Line::class,
			['number', 'quantity', 'order']
		);
		$this->assume(
			__METHOD__,
			$builder->buildQuery(),
			'SELECT t0.`number`, t0.`quantity`,'
			. ' t1.`date` AS `order:date`,'
			. ' t1.`has_workflow` AS `order:has_workflow`,'
			. ' t1.`number` AS `order:number`,'
			. ' t1.`id_client` AS `order:client`,'
			. ' t1.`id_delivery_client` AS `order:delivery_client`,'
			. ' t1.id AS `order:id`' . LF
			. 'FROM `orders_lines` t0' . LF
			. 'INNER JOIN `orders` t1 ON t1.id = t0.id_order'
		);
	}

	//----------------------------------------------------------------------- testObjectWhereWithNull
	public function testObjectWhereWithNull()
	{
		$search           = Search_Object::create(Order_Line::class);
		$search->client   = Func::isNull();
		$search->quantity = Func::greater(1);
		$builder          = new Select(
			Order_Line::class,
			['number', 'quantity'],
			$search
		);
		$this->assume(
			__METHOD__,
			$builder->buildQuery(),
			'SELECT t0.`number`, t0.`quantity`' . LF
			. 'FROM `orders_lines` t0' . LF
			. 'LEFT JOIN `clients` t1 ON t1.id = t0.id_client' . LF
			. 'WHERE t1.`id` IS NULL AND t0.`quantity` > 1'
		);
	}

	//-------------------------------------------------------------------------- testReverseJoinQuery
	public function testReverseJoinQuery()
	{
		$builder = new Select(
			Order::class,
			['date', 'number', 'Order_Line->order.number', 'Order_Line->order.quantity']
		);
		$this->assume(
			__METHOD__,
			$builder->buildQuery(),
			'SELECT t0.`date`, t0.`number`, t1.`number` AS `Order_Line->order.number`, t1.`quantity` AS `Order_Line->order.quantity`' . LF
			. 'FROM `orders` t0' . LF
			. 'LEFT JOIN `orders_lines` t1 ON t1.id_order = t0.id'
		);
	}

	//------------------------------------------------------------------------------- testSimpleQuery
	public function testSimpleQuery()
	{
		$builder = new Select(
			Order::class,
			['date', 'number']
		);
		$this->assume(
			__METHOD__,
			$builder->buildQuery(),
			'SELECT t0.`date`, t0.`number`' . LF
			. 'FROM `orders` t0'
		);
	}

	//------------------------------------------------------------------------- testWhereComplexQuery
	public function testWhereComplexQuery()
	{
		$client         = Search_Object::create(Client::class);
		$client->number = 1;
		$builder        = new Select(
			Order::class,
			['date', 'number', 'lines'],
			['OR' => ['lines.client.number' => $client->number, 'number' => 2]]
		);
		$this->assume(
			__METHOD__,
			$builder->buildQuery(),
			'SELECT t0.`date`, t0.`number`, t1.`id_client` AS `lines:client`, t1.`id_item` AS `lines:item`, t1.`number` AS `lines:number`, t1.`id_order` AS `lines:order`, t1.`quantity` AS `lines:quantity`, t1.id AS `lines:id`' . LF
			. 'FROM `orders` t0' . LF
			. 'INNER JOIN `orders_lines` t1 ON t1.id_order = t0.id' . LF
			. 'LEFT JOIN `clients` t2 ON t2.id = t1.id_client' . LF
			. 'WHERE (t2.`number` = 1 OR t0.`number` = 2)'
		);
	}

	//---------------------------------------------------------------------------- testWhereDeepQuery
	public function testWhereDeepQuery()
	{
		$builder = new Select(
			Order::class,
			['date', 'number'],
			['number' => 1, 'lines.number' => 2]
		);
		$this->assume(
			__METHOD__,
			$builder->buildQuery(),
			'SELECT t0.`date`, t0.`number`' . LF
			. 'FROM `orders` t0' . LF
			. 'INNER JOIN `orders_lines` t1 ON t1.id_order = t0.id' . LF
			. 'WHERE t0.`number` = 1 AND t1.`number` = 2'
		);
	}

	//------------------------------------------------------------------ testWhereExistingObjectQuery
	public function testWhereExistingObjectQuery()
	{
		/** @var $client Client */
		$client     = new Client();
		/** @noinspection PhpUndefinedFieldInspection */
		$client->id = 12;
		$builder    = new Select(
			Order::class,
			['date', 'number', 'lines'],
			['lines.client' => $client, 'number' => 2]
		);
		$this->assume(
			__METHOD__,
			$builder->buildQuery(),
			'SELECT t0.`date`, t0.`number`, t1.`id_client` AS `lines:client`, t1.`id_item` AS `lines:item`, t1.`number` AS `lines:number`, t1.`id_order` AS `lines:order`, t1.`quantity` AS `lines:quantity`, t1.id AS `lines:id`' . LF
			. 'FROM `orders` t0' . LF
			. 'INNER JOIN `orders_lines` t1 ON t1.id_order = t0.id' . LF
			. 'LEFT JOIN `clients` t2 ON t2.id = t1.id_client' . LF
			. 'WHERE t2.`id` = 12 AND t0.`number` = 2'
		);
	}

	//-------------------------------------------------------------------- testWhereSearchObjectQuery
	public function testWhereSearchObjectQuery()
	{
		/** @var $client Client */
		$client         = Search_Object::create(Client::class);
		$client->number = 1;
		$client->name   = 'Roger%';
		$properties     = ['number', 'name', 'client'];
		$builder        = new Select(Client::class, $properties, $client);
		$this->assume(
			__METHOD__,
			$builder->buildQuery(),
			'SELECT t0.`number`, t0.`name`, t1.`id_client` AS `client:client`, t1.`name` AS `client:name`, t1.`number` AS `client:number`, t1.id AS `client:id`' . LF
			. 'FROM `clients` t0' . LF
			. 'LEFT JOIN `clients` t1 ON t1.id = t0.id_client' . LF
			. 'WHERE t0.`name` LIKE "Roger%" AND t0.`number` = 1'
		);
	}

	//----------------------------------------------------------------- testWhereSubSearchObjectQuery
	public function testWhereSubSearchObjectQuery()
	{
		$client         = Search_Object::create(Client::class);
		$client->number = 1;
		$builder        = new Select(
			Order::class,
			['date', 'number', 'lines'],
			['lines.client' => $client, 'number' => 2]
		);
		$this->assume(
			__METHOD__,
			$builder->buildQuery(),
			'SELECT t0.`date`, t0.`number`, t1.`id_client` AS `lines:client`, t1.`id_item` AS `lines:item`, t1.`number` AS `lines:number`, t1.`id_order` AS `lines:order`, t1.`quantity` AS `lines:quantity`, t1.id AS `lines:id`' . LF
			. 'FROM `orders` t0' . LF
			. 'INNER JOIN `orders_lines` t1 ON t1.id_order = t0.id' . LF
			. 'LEFT JOIN `clients` t2 ON t2.id = t1.id_client' . LF
			. 'WHERE t2.`number` = 1 AND t0.`number` = 2'
		);
	}

	//-------------------------------------------------------------------------------- testWhereQuery
	public function testWhereQuery()
	{
		$builder = new Select(
			Order::class,
			['date', 'number'],
			['number' => 1]
		);
		$this->assume(
			__METHOD__,
			$builder->buildQuery(),
			'SELECT t0.`date`, t0.`number`' . LF
			. 'FROM `orders` t0' . LF
			. 'WHERE t0.`number` = 1'
		);
	}

	//--------------------------------------------------------------------- testWhereReverseJoinQuery
	public function testWhereReverseJoinQuery()
	{
		$builder = new Select(
			Order::class,
			['date', 'number', 'Order_Line->order.number', 'Order_Line->order.quantity'],
			['Order_Line->order.number' => '2']
		);
		$this->assume(
			__METHOD__,
			$builder->buildQuery(),
			'SELECT t0.`date`, t0.`number`, t1.`number` AS `Order_Line->order.number`, t1.`quantity` AS `Order_Line->order.quantity`' . LF
			. 'FROM `orders` t0' . LF
			. 'LEFT JOIN `orders_lines` t1 ON t1.id_order = t0.id' . LF
			. 'WHERE t1.`number` = 2'
		);
	}

}
