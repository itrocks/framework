<?php
namespace ITRocks\Framework\Sql\Join;

use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Sql\Join;
use ITRocks\Framework\Tests\Test;
use ITRocks\Framework\Tests\Objects\Client;
use ITRocks\Framework\Tests\Objects\Order;
use ITRocks\Framework\Tests\Objects\Order_Line;
use ITRocks\Framework\Tests\Objects\Salesman;

/**
 * Sql joins tests
 */
class Tests extends Test
{

	//-------------------------------------------------------------------------------- testCollection
	public function testCollection()
	{
		$assume = Join::newInstance(
			Join::INNER, 't0', 'id', 't1', 'orders_lines', 'id_order', Join::SIMPLE, Order_Line::class
		);
		$assume->foreign_property = new Reflection_Property(Order_Line::class, 'order');

		$this->assume(
			'one-level collection property (Order::lines.number)',
			Joins::newInstance(Order::class)
				->addMultiple(['date', 'number', 'lines.number', 'lines.quantity'])
				->getJoins(),
			[
				'date'           => null,
				'number'         => null,
				'lines'          => $assume,
				'lines.number'   => null,
				'lines.quantity' => null
			]
		);

		$assume1 = Join::newInstance(
			Join::INNER, 't0', 'id_client', 't1', 'clients', 'id', Join::SIMPLE, Client::class
		);
		$assume1->master_property = new Reflection_Property(Order::class, 'client');
		$assume2 = Join::newInstance(
			Join::LEFT,  't1', 'id_client', 't2', 'clients', 'id', Join::SIMPLE, Client::class
		);
		$assume2->master_property = new Reflection_Property(Client::class, 'client');

		$this->assume(
			'multi-levels collection (Order::client.number and Order::client.client.number)',
			Joins::newInstance(Order::class)
				->addMultiple(['number', 'client.number', 'client.client.number', 'client.name'])
				->getJoins(),
			[
				'number'               => null,
				'client'               => $assume1,
				'client.number'        => null,
				'client.client'        => $assume2,
				'client.client.number' => null,
				'client.name'          => null
			]
		);
	}

	//-------------------------------------------------------------------------------------- testJoin
	public function testJoin()
	{
		$assume = Join::newInstance(
			Join::INNER, 't0', 'id_order', 't1', 'orders', 'id', Join::SIMPLE, Order::class
		);
		$assume->master_property = new Reflection_Property(Order_Line::class, 'order');

		$this->assume(
			'simple join (Order_Line::order.date)',
			Joins::newInstance(Order_Line::class)
				->addMultiple(['order.date', 'order.number', 'number', 'quantity'])
				->getJoins(),
			[
				'order'        => $assume,
				'order.date'   => null,
				'order.number' => null,
				'number'       => null,
				'quantity'     => null
			]
		);
	}

	//--------------------------------------------------------------------------------------- testMap
	public function testMap()
	{
		$assume = Join::newInstance(
			Join::LEFT, 't1', 'id_salesman', 't2', 'test_salesmen', 'id', Join::SIMPLE, Salesman::class
		);
		$assume->linked_join = Join::newInstance(
			Join::LEFT, 't0', 'id', 't1', 'orders_test_salesmen', 'id_order', Join::SIMPLE
		);
		$assume->master_property = new Reflection_Property(Order::class, 'salesmen');

		$this->assume(
			'one-level map property (Order::salesmen.name)',
			$joins = Joins::newInstance(Order::class)
				->addMultiple(['date', 'number', 'salesmen.name'])
				->getJoins(),
			[
				'date' => null,
				'number' => null,
				'salesmen-link' => Join::newInstance(
					Join::LEFT, 't0', 'id', 't1', 'orders_test_salesmen', 'id_order'
				),
				'salesmen' => $assume,
				'salesmen.name' => null
			]
		);
	}

	//------------------------------------------------------------------------------------ testObject
	public function testObject()
	{
		$assume = Join::newInstance(
			Join::INNER, 't0', 'id_order', 't1', 'orders', 'id',
			Join::OBJECT, Order::class
		);
		$assume->master_property = new Reflection_Property(Order_Line::class, 'order');

		$this->assume(
			'object property (Order_Line::order)',
			Joins::newInstance(Order_Line::class)
				->addMultiple(['number', 'quantity', 'order'])
				->getJoins(),
			[
				'number'   => null,
				'quantity' => null,
				'order'    => $assume
			]
		);
	}

	//----------------------------------------------------------------------------------- testReverse
	public function testReverse()
	{
		$assume = Join::newInstance(
			Join::LEFT, 't0', 'id', 't1', 'orders_lines', 'id_order', Join::SIMPLE, Order_Line::class
		);
		$assume->foreign_property = new Reflection_Property(Order_Line::class, 'order');

		$this->assume(
			'reverse join (Order::Order_Line->order.number)',
			Joins::newInstance(Order::class)
				->addMultiple(['date', 'number', 'Order_Line->order.number', 'Order_Line->order.quantity'])
				->getJoins(),
			[
				'date'                       => null,
				'number'                     => null,
				'Order_Line->order'          => $assume,
				'Order_Line->order.number'   => null,
				'Order_Line->order.quantity' => null
			]
		);

		$assume_client = Join::newInstance(
			Join::LEFT, 't0', 'id', 't1', 'orders_lines', 'id_client', Join::SIMPLE, Order_Line::class
		);
		$assume_client->foreign_property = new Reflection_Property(Order_Line::class, 'client');
		$assume_order = Join::newInstance(
			Join::LEFT, 't1', 'id_order', 't2', 'orders', 'id',
			Join::OBJECT, Order::class
		);
		$assume_order->master_property = new Reflection_Property(Order_Line::class, 'order');

		$this->assume(
			'reverse object (Client::Order_Line->client.order)',
			Joins::newInstance(Client::class)
				->addMultiple(['number', 'name', 'Order_Line->client.order'])
				->getJoins(),
			[
				'number'                   => null,
				'name'                     => null,
				'Order_Line->client'       => $assume_client,
				'Order_Line->client.order' => $assume_order
			]
		);

		$assume = Join::newInstance(
			Join::LEFT, 't1', 'id_order', 't2', 'orders', 'id', Join::SIMPLE, Order::class
		);
		$assume->linked_join = Join::newInstance(
			Join::LEFT, 't0', 'id', 't1', 'orders_test_salesmen', 'id_salesman', Join::SIMPLE
		);
		$assume->master_property = new Reflection_Property(Order::class, 'salesmen');

		$this->assume(
			'reverse map (Salesman::Order->salesmen.number)',
			Joins::newInstance(Salesman::class)
				->addMultiple(['Order->salesmen.number', 'name'])
				->getJoins(),
			[
				'Order->salesmen-link' => Join::newInstance(
					Join::LEFT, 't0', 'id', 't1', 'orders_test_salesmen', 'id_salesman'
				),
				'Order->salesmen' => $assume,
				'Order->salesmen.number' => null,
				'name' => null
			]
		);
	}

	//------------------------------------------------------------------------------------ testSimple
	public function testSimple()
	{
		$this->assume(
			'simple properties (Order::number)',
			Joins::newInstance(Order::class)
				->addMultiple(['date', 'number'])
				->getJoins(),
			['date' => null, 'number' => null]
		);
	}

}
