<?php
namespace SAF\Framework\Reflection\Tests;

use ReflectionException;
use SAF\Framework\Reflection\Reflection_Class;
use SAF\Framework\Reflection\Reflection_Property;
use SAF\Framework\Tests\Objects\Document;
use SAF\Framework\Tests\Objects\Order;
use SAF\Framework\Tests\Test;

/**
 * Reflection tests
 */
class Reflection_Class_Tests extends Test
{

	//-------------------------------------------------------------------------- testAccessProperties
	public function testAccessProperties()
	{
		// does access properties return properties list ?
		$class = new Reflection_Class(Order::class);
		$date = new Reflection_Property(Document::class, 'date');
		$date->final_class = Order::class;
		$number = new Reflection_Property(Document::class, 'number');
		$number->final_class = Order::class;
		$has_workflow = new Reflection_Property(Document::class, 'has_workflow');
		$has_workflow->final_class = Order::class;
		$test1 = $this->assume(
			__METHOD__ . '.1',
			$properties = $class->accessProperties(),
			[
				'date'            => $date,
				'number'          => $number,
				'has_workflow'    => $has_workflow,
				'client'          => new Reflection_Property(Order::class,    'client'),
				'delivery_client' => new Reflection_Property(Order::class,    'delivery_client'),
				'lines'           => new Reflection_Property(Order::class,    'lines'),
				'salesmen'        => new Reflection_Property(Order::class,    'salesmen')
			]
		);
		if ($test1) {
			// are properties now accessible ?
			$check = [];
			$test_order = new Order(date('Y-m-d'), 'CDE001');
			$test_order->has_workflow = true;
			foreach ($properties as $property) {
				try {
					$check[$property->name] = $property->getValue($test_order);
				}
				catch (ReflectionException $e) {
					$check[$property->name] = null;
				}
			}
			$this->assume(
				__METHOD__ . '.2',
				$check,
				[
					'date' => date('Y-m-d'), 'number' => 'CDE001', 'has_workflow' => true, 'client' => null,
					'lines' => null
				]
			);
		}
	}

	//---------------------------------------------------------------------- testAccessPropertiesDone
	public function testAccessPropertiesDone()
	{
		$test_order = new Order(date('Y-m-d'), 'CDE001');
		$test_order->has_workflow = true;
		$class = new Reflection_Class(Order::class);
		$class->accessProperties();
		$properties = $class->getProperties([T_EXTENDS, T_USE]);
		$check = [];
		foreach ($properties as $property) {
			try {
				$check[$property->name] = $property->getValue($test_order);
			}
			catch (ReflectionException $e) {
				$check[$property->name] = null;
			}
		}
		$this->assume(
			__METHOD__,
			$check,
			[
				'date' => null, 'has_workflow' => 1, 'number' => null, 'client' => null,
				'delivery_client' => null, 'lines' => null, 'salesmen' => null
			]
		);
	}

	//-------------------------------------------------------------------------- testGetAllProperties
	public function testGetAllProperties()
	{
		$date = new Reflection_Property(Document::class, 'date');
		$date->final_class = Order::class;
		$number = new Reflection_Property(Document::class, 'number');
		$number->final_class = Order::class;
		$has_workflow = new Reflection_Property(Document::class, 'has_workflow');
		$has_workflow->final_class = Order::class;
		$this->assume(
			__METHOD__,
			(new Reflection_Class(Order::class))->getProperties([T_EXTENDS, T_USE]),
			[
				'date'            => $date,
				'number'          => $number,
				'has_workflow'    => $has_workflow,
				'client'          => new Reflection_Property(Order::class, 'client'),
				'delivery_client' => new Reflection_Property(Order::class, 'delivery_client'),
				'lines'           => new Reflection_Property(Order::class, 'lines'),
				'salesmen'        => new Reflection_Property(Order::class, 'salesmen')
			]
		);
	}

}
