<?php
namespace SAF\Framework\Sql\Join;

use SAF\Framework\Sql\Join;
use SAF\Framework\Tests\Test;
use SAF\Framework\Tests\Objects\Client;
use SAF\Framework\Tests\Objects\Order;
use SAF\Framework\Tests\Objects\Order_Line;
use SAF\Framework\Tests\Objects\Salesman;

/**
 * Sql joins tests
 */
class Tests extends Test
{

	//-------------------------------------------------------------------------------- testCollection
	public function testCollection()
	{
		$this->assume(
			'one-level collection property (Order::lines.number)',
			Joins::newInstance(Order::class)
				->addMultiple(['date', 'number', 'lines.number', 'lines.quantity'])
				->getJoins(),
			[
				'date' => null,
				'number' => null,
				'lines' => Join::newInstance(
					Join::INNER, 't0', 'id', 't1', 'orders_lines', 'id_order',
					Join::SIMPLE, Order_Line::class
				),
				'lines.number' => null,
				'lines.quantity' => null
			]
		);
		$this->assume(
			'multi-levels collection (Order::client.number and Order::client.client.number)',
			Joins::newInstance(Order::class)
				->addMultiple(['number', 'client.number', 'client.client.number', 'client.name'])
				->getJoins(),
			[
				'number' => null,
				'client' => Join::newInstance(
					Join::INNER, 't0', 'id_client', 't1', 'clients', 'id',
					Join::SIMPLE, Client::class
				),
				'client.number' => null,
				'client.client' => Join::newInstance(
					Join::LEFT,  't1', 'id_client', 't2', 'clients', 'id',
					Join::SIMPLE, Client::class
				),
				'client.client.number' => null,
				'client.name' => null
			]
		);
	}

	//-------------------------------------------------------------------------------------- testJoin
	public function testJoin()
	{
		$this->assume(
			'simple join (Order_Line::order.date)',
			Joins::newInstance(Order_Line::class)
				->addMultiple(['order.date', 'order.number', 'number', 'quantity'])
				->getJoins(),
			[
				'order' => Join::newInstance(
					Join::INNER, 't0', 'id_order', 't1', 'orders', 'id',
					Join::SIMPLE, Order::class
				),
				'order.date' => null,
				'order.number' => null,
				'number' => null,
				'quantity' => null
			]
		);
	}

	//--------------------------------------------------------------------------------------- testMap
	public function testMap()
	{
		$this->assume(
			'one-level map property (Order::salesmen.name)',
			$joins = Joins::newInstance(Order::class)
				->addMultiple(['date', 'number', 'salesmen.name'])
				->getJoins(),
			[
				'date' => null,
				'number' => null,
				'salesmen-link' => Join::newInstance(
					Join::LEFT, 't0', 'id', 't1', 'orders_salesmen', 'id_order'
				),
				'salesmen' => Join::newInstance(
					Join::LEFT, 't1', 'id_salesman', 't2', 'salesmen', 'id',
					Join::SIMPLE, Salesman::class
				),
				'salesmen.name' => null
			]
		);
	}

	//------------------------------------------------------------------------------------ testObject
	public function testObject()
	{
		$this->assume(
			'object property (Order_Line::order)',
			Joins::newInstance(Order_Line::class)
				->addMultiple(['number', 'quantity', 'order'])
				->getJoins(),
			[
				'number' => null,
				'quantity' => null,
				'order' => Join::newInstance(
					Join::INNER, 't0', 'id_order', 't1', 'orders', 'id',
					Join::OBJECT, Order::class
				)
			]
		);
	}

	//----------------------------------------------------------------------------------- testReverse
	public function testReverse()
	{
		$this->assume(
			'reverse join (Order::Order_Line->order.number)',
			Joins::newInstance(Order::class)
				->addMultiple(['date', 'number', 'Order_Line->order.number', 'Order_Line->order.quantity'])
				->getJoins(),
			[
				'date' => null,
				'number' => null,
				'Order_Line->order' => Join::newInstance(
					Join::LEFT, 't0', 'id', 't1', 'orders_lines', 'id_order',
					Join::SIMPLE, Order_Line::class
				),
				'Order_Line->order.number' => null,
				'Order_Line->order.quantity' => null
			]
		);
		$this->assume(
			'reverse object (Client::Order_Line->client.order)',
			Joins::newInstance(Client::class)
				->addMultiple(['number', 'name', 'Order_Line->client.order'])
				->getJoins(),
			[
				'number' => null,
				'name' => null,
				'Order_Line->client' => Join::newInstance(
					Join::LEFT, 't0', 'id', 't1', 'orders_lines', 'id_client',
					Join::SIMPLE, Order_Line::class
				),
				'Order_Line->client.order' => Join::newInstance(
					Join::INNER, 't1', 'id_order', 't2', 'orders', 'id',
					Join::OBJECT, Order::class
				)
			]
		);
		$this->assume(
			'reverse map (Salesman::Order->salesmen.number)',
			Joins::newInstance(Salesman::class)
				->addMultiple(['Order->salesmen.number', 'name'])
				->getJoins(),
			[
				'Order->salesmen-link' => Join::newInstance(
					Join::LEFT, 't0', 'id', 't1', 'orders_salesmen', 'id_salesman'
				),
				'Order->salesmen' => Join::newInstance(
					Join::LEFT, 't1', 'id_order', 't2', 'orders', 'id',
					Join::SIMPLE, Order::class
				),
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
