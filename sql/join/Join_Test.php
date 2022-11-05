<?php
namespace ITRocks\Framework\Sql\Join;

use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Sql\Join;
use ITRocks\Framework\Tests\Objects\Client;
use ITRocks\Framework\Tests\Objects\Order;
use ITRocks\Framework\Tests\Objects\Order_Line;
use ITRocks\Framework\Tests\Objects\Salesman;
use ITRocks\Framework\Tests\Test;

/**
 * Sql joins tests
 */
class Join_Test extends Test
{

	//-------------------------------------------------------------------------------- testCollection
	public function testCollection() : void
	{
		$assume = Join::newInstance(
			Join::INNER, 't0', 'id', 't1', 'test_order_lines', 'id_order',
			Join::SIMPLE, Order_Line::class
		);
		$assume->foreign_property = new Reflection_Property(Order_Line::class, 'order');

		static::assertEquals(
			[
				'date'           => null,
				'number'         => null,
				'lines'          => $assume,
				'lines.number'   => null,
				'lines.quantity' => null
			],
			Joins::newInstance(Order::class)
				->addMultiple(['date', 'number', 'lines.number', 'lines.quantity'])
				->getJoins(),
			'one-level collection property (Order::lines.number)'
		);

		$assume1 = Join::newInstance(
			Join::INNER, 't0', 'id_client', 't1', 'test_clients', 'id', Join::SIMPLE, Client::class
		);
		$assume1->master_property = new Reflection_Property(Order::class, 'client');
		$assume2 = Join::newInstance(
			Join::LEFT,  't1', 'id_client', 't2', 'test_clients', 'id', Join::SIMPLE, Client::class
		);
		$assume2->master_property = new Reflection_Property(Client::class, 'client');

		static::assertEquals(
			[
				'number'               => null,
				'client'               => $assume1,
				'client.number'        => null,
				'client.client'        => $assume2,
				'client.client.number' => null,
				'client.name'          => null
			],
			Joins::newInstance(Order::class)
				->addMultiple(['number', 'client.number', 'client.client.number', 'client.name'])
				->getJoins(),
			'multi-levels collection (Order::client.number and Order::client.client.number)'
		);
	}

	//-------------------------------------------------------------------------------------- testJoin
	public function testJoin() : void
	{
		$assume = Join::newInstance(
			Join::INNER, 't0', 'id_order', 't1', 'test_orders', 'id', Join::SIMPLE, Order::class
		);
		$assume->master_property = new Reflection_Property(Order_Line::class, 'order');

		static::assertEquals(
			[
				'order'        => $assume,
				'order.date'   => null,
				'order.number' => null,
				'number'       => null,
				'quantity'     => null
			],
			Joins::newInstance(Order_Line::class)
				->addMultiple(['order.date', 'order.number', 'number', 'quantity'])
				->getJoins(),
			'simple join (Order_Line::order.date)'
		);
	}

	//--------------------------------------------------------------------------------------- testMap
	public function testMap() : void
	{
		$assume = Join::newInstance(
			Join::LEFT, 't1', 'id_salesman', 't2', 'test_salesmen', 'id', Join::SIMPLE, Salesman::class
		);
		$assume->linked_join = Join::newInstance(
			Join::LEFT, 't0', 'id', 't1', 'test_orders_salesmen', 'id_order'
		);
		$assume->master_property = new Reflection_Property(Order::class, 'salesmen');

		static::assertEquals(
			[
				'date'          => null,
				'number'        => null,
				'salesmen-link' => Join::newInstance(
					Join::LEFT, 't0', 'id', 't1', 'test_orders_salesmen', 'id_order'
				),
				'salesmen'      => $assume,
				'salesmen.name' => null
			],
			Joins::newInstance(Order::class)
				->addMultiple(['date', 'number', 'salesmen.name'])
				->getJoins(),
			'one-level map property (Order::salesmen.name)'
		);
	}

	//------------------------------------------------------------------------------------ testObject
	public function testObject() : void
	{
		$assume = Join::newInstance(
			Join::INNER, 't0', 'id_order', 't1', 'test_orders', 'id',
			Join::OBJECT, Order::class
		);
		$assume->master_property = new Reflection_Property(Order_Line::class, 'order');

		static::assertEquals(
			[
				'number'   => null,
				'quantity' => null,
				'order'    => $assume
			],
			Joins::newInstance(Order_Line::class)
				->addMultiple(['number', 'quantity', 'order'])
				->getJoins(),
			'object property (Order_Line::order)'
		);
	}

	//----------------------------------------------------------------------------------- testReverse
	public function testReverse() : void
	{
		$assume = Join::newInstance(
			Join::LEFT, 't0', 'id', 't1', 'test_order_lines', 'id_order', Join::SIMPLE, Order_Line::class
		);
		$assume->foreign_property = new Reflection_Property(Order_Line::class, 'order');

		static::assertEquals(
			[
				'date'                       => null,
				'number'                     => null,
				'Order_Line(order)'          => $assume,
				'Order_Line(order).number'   => null,
				'Order_Line(order).quantity' => null
			],
			Joins::newInstance(Order::class)
				->addMultiple(['date', 'number', 'Order_Line(order).number', 'Order_Line(order).quantity'])
				->getJoins(),
			'reverse join (Order::Order_Line(order).number)'
		);

		$assume_client = Join::newInstance(
			Join::LEFT, 't0', 'id', 't1', 'test_order_lines', 'id_client', Join::SIMPLE, Order_Line::class
		);
		$assume_client->foreign_property = new Reflection_Property(Order_Line::class, 'client');
		$assume_order = Join::newInstance(
			Join::LEFT, 't1', 'id_order', 't2', 'test_orders', 'id',
			Join::OBJECT, Order::class
		);
		$assume_order->master_property = new Reflection_Property(Order_Line::class, 'order');

		static::assertEquals(
			[
				'number'                   => null,
				'name'                     => null,
				'Order_Line(client)'       => $assume_client,
				'Order_Line(client).order' => $assume_order
			],
			Joins::newInstance(Client::class)
				->addMultiple(['number', 'name', 'Order_Line(client).order'])
				->getJoins(),
			'reverse object (Client::Order_Line(client).order)'
		);

		$assume = Join::newInstance(
			Join::LEFT, 't1', 'id_order', 't2', 'test_orders', 'id', Join::SIMPLE, Order::class
		);
		$assume->linked_join = Join::newInstance(
			Join::LEFT, 't0', 'id', 't1', 'test_orders_salesmen', 'id_salesman'
		);
		$assume->master_property = new Reflection_Property(Order::class, 'salesmen');

		static::assertEquals(
			[
				'Order(salesmen)-link' => Join::newInstance(
					Join::LEFT, 't0', 'id', 't1', 'test_orders_salesmen', 'id_salesman'
				),
				'Order(salesmen)'        => $assume,
				'Order(salesmen).number' => null,
				'name'                   => null
			],
			Joins::newInstance(Salesman::class)
				->addMultiple(['Order(salesmen).number', 'name'])
				->getJoins(),
			'reverse map (Salesman::Order(salesmen).number)'
		);
	}

	//------------------------------------------------------------------------------------ testSimple
	public function testSimple() : void
	{
		static::assertEquals(
			['date' => null, 'number' => null],
			Joins::newInstance(Order::class)
				->addMultiple(['date', 'number'])
				->getJoins(),
			'simple properties (Order::number)'
		);
	}

}
