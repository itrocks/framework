<?php
namespace SAF\Tests\Tests;

use SAF\Framework\Sql_Join;
use SAF\Framework\Sql_Joins;
use SAF\Framework\Unit_Tests\Unit_Test;
use SAF\Tests;

/**
 * Sql joins tests
 */
class Sql_Joins_Test extends Unit_Test
{

	//-------------------------------------------------------------------------------- testCollection
	public function testCollection()
	{
		$this->assume(
			'one-level collection property (Order::lines.number)',
			Sql_Joins::newInstance(Tests\Order::class)
				->addMultiple(['date', 'number', 'lines.number', 'lines.quantity'])
				->getJoins(),
			[
				'date' => null,
				'number' => null,
				'lines' => Sql_Join::newInstance(
					Sql_Join::INNER, 't0', 'id', 't1', 'orders_lines', 'id_order',
					Sql_Join::SIMPLE, Tests\Order_Line::class
				),
				'lines.number' => null,
				'lines.quantity' => null
			]
		);
		$this->assume(
			'multi-levels collection (Order::client.number and Order::client.client.number)',
			Sql_Joins::newInstance(Tests\Order::class)
				->addMultiple(['number', 'client.number', 'client.client.number', 'client.name'])
				->getJoins(),
			[
				'number' => null,
				'client' => Sql_Join::newInstance(
					Sql_Join::INNER, 't0', 'id_client', 't1', 'clients', 'id',
					Sql_Join::SIMPLE, Tests\Client::class
				),
				'client.number' => null,
				'client.client' => Sql_Join::newInstance(
					Sql_Join::LEFT,  't1', 'id_client', 't2', 'clients', 'id',
					Sql_Join::SIMPLE, Tests\Client::class
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
			Sql_Joins::newInstance(Tests\Order_Line::class)
				->addMultiple(['order.date', 'order.number', 'number', 'quantity'])
				->getJoins(),
			[
				'order' => Sql_Join::newInstance(
					Sql_Join::INNER, 't0', 'id_order', 't1', 'orders', 'id',
					Sql_Join::SIMPLE, Tests\Order::class
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
			$joins = Sql_Joins::newInstance(Tests\Order::class)
				->addMultiple(['date', 'number', 'salesmen.name'])
				->getJoins(),
			[
				'date' => null,
				'number' => null,
				'salesmen-link' => Sql_Join::newInstance(
					Sql_Join::LEFT, 't0', 'id', 't1', 'orders_salesmen', 'id_order'
				),
				'salesmen' => Sql_Join::newInstance(
					Sql_Join::LEFT, 't1', 'id_salesman', 't2', 'salesmen', 'id',
					Sql_Join::SIMPLE, Tests\Salesman::class
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
			Sql_Joins::newInstance(Tests\Order_Line::class)
				->addMultiple(['number', 'quantity', 'order'])
				->getJoins(),
			[
				'number' => null,
				'quantity' => null,
				'order' => Sql_Join::newInstance(
					Sql_Join::INNER, 't0', 'id_order', 't1', 'orders', 'id',
					Sql_Join::OBJECT, Tests\Order::class
				)
			]
		);
	}

	//----------------------------------------------------------------------------------- testReverse
	public function testReverse()
	{
		$this->assume(
			'reverse join (Order::Order_Line->order.number)',
			Sql_Joins::newInstance(Tests\Order::class)
				->addMultiple(['date', 'number', 'Order_Line->order.number', 'Order_Line->order.quantity'])
				->getJoins(),
			[
				'date' => null,
				'number' => null,
				'Order_Line->order' => Sql_Join::newInstance(
					Sql_Join::LEFT, 't0', 'id', 't1', 'orders_lines', 'id_order',
					Sql_Join::SIMPLE, Tests\Order_Line::class
				),
				'Order_Line->order.number' => null,
				'Order_Line->order.quantity' => null
			]
		);
		$this->assume(
			'reverse object (Client::Order_Line->client.order)',
			Sql_Joins::newInstance(Tests\Client::class)
				->addMultiple(['number', 'name', 'Order_Line->client.order'])
				->getJoins(),
			[
				'number' => null,
				'name' => null,
				'Order_Line->client' => Sql_Join::newInstance(
					Sql_Join::LEFT, 't0', 'id', 't1', 'orders_lines', 'id_client',
					Sql_Join::SIMPLE, Tests\Order_Line::class
				),
				'Order_Line->client.order' => Sql_Join::newInstance(
					Sql_Join::INNER, 't1', 'id_order', 't2', 'orders', 'id',
					Sql_Join::OBJECT, Tests\Order::class
				)
			]
		);
		$this->assume(
			'reverse map (Salesman::Order->salesmen.number)',
			Sql_Joins::newInstance(Tests\Salesman::class)
				->addMultiple(['SAF\Tests\Order->salesmen.number', 'name'])
				->getJoins(),
			[
				'SAF\Tests\Order->salesmen-link' => Sql_Join::newInstance(
					Sql_Join::LEFT, 't0', 'id', 't1', 'orders_salesmen', 'id_salesman'
				),
				'SAF\Tests\Order->salesmen' => Sql_Join::newInstance(
					Sql_Join::LEFT, 't1', 'id_order', 't2', 'orders', 'id',
					Sql_Join::SIMPLE, Tests\Order::class
				),
				'SAF\Tests\Order->salesmen.number' => null,
				'name' => null
			]
		);
	}

	//------------------------------------------------------------------------------------ testSimple
	public function testSimple()
	{
		$this->assume(
			'simple properties (Order::number)',
			Sql_Joins::newInstance(Tests\Order::class)
				->addMultiple(['date', 'number'])
				->getJoins(),
			['date' => null, 'number' => null]
		);
	}

}
