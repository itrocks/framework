<?php
namespace ITRocks\Framework\Reflection\Tests;

use ReflectionException;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Tests\Objects\Document;
use ITRocks\Framework\Tests\Objects\Order;
use ITRocks\Framework\Tests\Test;

/**
 * Reflection class tests
 */
class Reflection_Class_Tests extends Test
{

	//---------------------------------------------------------------------------------- INACCESSIBLE
	const INACCESSIBLE = 'inaccessible';

	//------------------------------------------------------------------------------------ properties
	/**
	 * @param $date   Reflection_Property
	 * @param $number Reflection_Property
	 * @return Reflection_Property[]
	 */
	private function properties(Reflection_Property $date, Reflection_Property $number)
	{
		return [
			'date'            => $date,
			'number'          => $number,
			'has_workflow'    => new Reflection_Property(Order::class, 'has_workflow'),
			'client'          => new Reflection_Property(Order::class, 'client'),
			'delivery_client' => new Reflection_Property(Order::class, 'delivery_client'),
			'lines'           => new Reflection_Property(Order::class, 'lines'),
			'salesmen'        => new Reflection_Property(Order::class, 'salesmen')
		];
	}

	//-------------------------------------------------------------------------- shouldBeInaccessible
	/**
	 * @param $method     string
	 * @param $class      Reflection_Class
	 * @param $test_order Order
	 */
	private function shouldBeInaccessible($method, Reflection_Class $class, Order $test_order)
	{
		$check = [];
		foreach ($class->getProperties([T_EXTENDS, T_USE]) as $property) {
			try {
				$check[$property->name] = $property->getValue($test_order);
			}
			catch (ReflectionException $e) {
				$check[$property->name] = self::INACCESSIBLE;
			}
		}
		$this->assume(
			$method,
			$check,
			[
				'date'            => self::INACCESSIBLE,
				'delivery_client' => self::INACCESSIBLE,
				'number'          => self::INACCESSIBLE,
				'has_workflow'    => true,
				'client'          => self::INACCESSIBLE,
				'lines'           => self::INACCESSIBLE,
				'salesmen'        => self::INACCESSIBLE
			]
		);
	}

	//-------------------------------------------------------------------------- testAccessProperties
	public function testAccessProperties()
	{
		$class = new Reflection_Class(Order::class);
		$today = date('Y-m-d');
		$test_order = new Order($today, 'CDE001');
		$test_order->has_workflow = true;

		// all properties should not be accessible from an order
		$this->shouldBeInaccessible(__METHOD__ . '.1 (getAllProperties)', $class, $test_order);

		// does access properties return properties list ?
		$date   = new Reflection_Property(Document::class, 'date');
		$number = new Reflection_Property(Document::class, 'number');
		$date->final_class = $number->final_class = Order::class;
		$test2 = $this->assume(
			__METHOD__ . '2 (accessProperties)',
			$properties = $class->accessProperties(),
			$this->properties($date, $number)
		);
		if ($test2) {
			// are properties now accessible from an order ?
			$check = [];
			foreach ($properties as $property) {
				try {
					$check[$property->name] = $property->getValue($test_order);
				}
				catch (ReflectionException $e) {
					$check[$property->name] = 'inaccessible';
				}
			}
			$this->assume(
				__METHOD__ . '.3 (accessProperties, then getValue)',
				$check,
				[
					'date'            => $today,
					'delivery_client' => null,
					'number'          => 'CDE001',
					'has_workflow'    => true,
					'client'          => null,
					'lines'           => [],
					'salesmen'        => []
				]
			);
		}
		// properties should not be accessible, again
		$class->accessPropertiesDone();
		$this->shouldBeInaccessible(__METHOD__ . '.4 (accessPropertiesDone)', $class, $test_order);
	}

	//---------------------------------------------------------------------- testAccessPropertiesDone
	public function testAccessPropertiesDone()
	{
		$class = new Reflection_Class(Order::class);
		$test_order = new Order(date('Y-m-d'), 'CDE001');
		$test_order->has_workflow = true;

		$class->accessProperties();
		$this->shouldBeInaccessible(__METHOD__, $class, $test_order);
		$class->accessPropertiesDone();
	}

	//-------------------------------------------------------------------------- testGetAllProperties
	/**
	 * Test get all properties
	 */
	public function testGetAllProperties()
	{
		$date   = new Reflection_Property(Document::class, 'date');
		$number = new Reflection_Property(Document::class, 'number');
		$date->final_class = $number->final_class = Order::class;
		$this->assume(
			__METHOD__,
			(new Reflection_Class(Order::class))->getProperties([T_EXTENDS, T_USE]),
			$this->properties($date, $number)
		);
	}

}
