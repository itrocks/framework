<?php
namespace SAF\Tests\Test;

use ReflectionException;
use SAF\Framework\Reflection_Class;
use SAF\Framework\Reflection_Property;
use SAF\Framework\Unit_Tests\Unit_Test;
use SAF\Tests\Document;
use SAF\Tests\Order;

/**
 * Reflection tests
 */
class Reflection extends Unit_Test
{

	//----------------------------------------------------------------------- testAccessProperties
	public function testAccessProperties()
	{
		// does access properties return properties list ?
		$class = new Reflection_Class(Order::class);
		$date = new Reflection_Property(Document::class, 'date');
		$date->final_class = Order::class;
		$number = new Reflection_Property(Document::class, 'number');
		$number->final_class = Order::class;
		$test1 = $this->assume(
			__METHOD__ . '.1',
			$properties = $class->accessProperties(),
			[
				'date'            => $date,
				'number'          => $number,
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
				['date' => date('Y-m-d'), 'number' => 'CDE001', 'client' => null, 'lines' => null]
			);
		}
	}

	//------------------------------------------------------------------- testAccessPropertiesDone
	public function testAccessPropertiesDone()
	{
		$test_order = new Order(date('Y-m-d'), 'CDE001');
		$class = new Reflection_Class(Order::class);
		$class->accessProperties();
		$properties = $class->getAllProperties();
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
				'date' => null, 'number' => null, 'client' => null, 'delivery_client' => null,
				'lines' => null, 'salesmen' => null
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
		$this->assume(
			__METHOD__,
			(new Reflection_Class(Order::class))->getAllProperties(),
			[
				'date'            => $date,
				'number'          => $number,
				'client'          => new Reflection_Property(Order::class,    'client'),
				'delivery_client' => new Reflection_Property(Order::class,    'delivery_client'),
				'lines'           => new Reflection_Property(Order::class,    'lines'),
				'salesmen'        => new Reflection_Property(Order::class,    'salesmen')
			]
		);
	}

}
