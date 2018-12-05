<?php
namespace ITRocks\Framework\Reflection\Tests;

use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Tests\Objects\Document;
use ITRocks\Framework\Tests\Objects\Order;
use ITRocks\Framework\Tests\Test;
use ReflectionException;

/**
 * Reflection class tests
 */
class Reflection_Class_Test extends Test
{

	//---------------------------------------------------------------------------------- INACCESSIBLE
	const INACCESSIBLE = 'inaccessible';

	//------------------------------------------------------------------------------------ properties
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $date   Reflection_Property
	 * @param $number Reflection_Property
	 * @return Reflection_Property[]
	 */
	private function properties(Reflection_Property $date, Reflection_Property $number)
	{
		/** @noinspection PhpUnhandledExceptionInspection valid constant properties of Order */
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
			catch (ReflectionException $exception) {
				$check[$property->name] = self::INACCESSIBLE;
			}
		}
		static::assertEquals(
			[
				'date'            => self::INACCESSIBLE,
				'delivery_client' => self::INACCESSIBLE,
				'number'          => self::INACCESSIBLE,
				'has_workflow'    => true,
				'client'          => self::INACCESSIBLE,
				'lines'           => self::INACCESSIBLE,
				'salesmen'        => self::INACCESSIBLE
			],
			$check,
			$method
		);
	}

	//-------------------------------------------------------------------------- testAccessProperties
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public function testAccessProperties()
	{
		/** @noinspection PhpUnhandledExceptionInspection constant class name */
		$class                    = new Reflection_Class(Order::class);
		$today                    = date('Y-m-d');
		$test_order               = new Order($today, 'CDE001');
		$test_order->has_workflow = true;

		// all properties should not be accessible from an order
		$this->shouldBeInaccessible(__METHOD__ . '.1 (getAllProperties)', $class, $test_order);

		// does access properties return properties list ?
		/** @noinspection PhpUnhandledExceptionInspection valid constant property name */
		$date = new Reflection_Property(Document::class, 'date');
		/** @noinspection PhpUnhandledExceptionInspection valid constant property name */
		$number = new Reflection_Property(Document::class, 'number');

		$date->final_class = $number->final_class = Order::class;
		static::assertEquals(
			$this->properties($date, $number),
			$properties = $class->accessProperties(),
			__METHOD__ . '2 (accessProperties)'
		);

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
		static::assertEquals(
			[
				'date'            => $today,
				'delivery_client' => null,
				'number'          => 'CDE001',
				'has_workflow'    => true,
				'client'          => null,
				'lines'           => [],
				'salesmen'        => []
			],
			$check,
			__METHOD__ . '.3 (accessProperties, then getValue)'
		);

		// properties should not be accessible, again
		$class->accessPropertiesDone();
		$this->shouldBeInaccessible(__METHOD__ . '.4 (accessPropertiesDone)', $class, $test_order);
	}

	//---------------------------------------------------------------------- testAccessPropertiesDone
	/**
	 * Test access properties done
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public function testAccessPropertiesDone()
	{
		/** @noinspection PhpUnhandledExceptionInspection constant class name */
		$class                    = new Reflection_Class(Order::class);
		$test_order               = new Order(date('Y-m-d'), 'CDE001');
		$test_order->has_workflow = true;

		$class->accessProperties();
		$this->shouldBeInaccessible(__METHOD__, $class, $test_order);
		$class->accessPropertiesDone();
	}

	//-------------------------------------------------------------------------- testGetAllProperties
	/**
	 * Test get all properties
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public function testGetAllProperties()
	{
		/** @noinspection PhpUnhandledExceptionInspection constant property */
		$date = new Reflection_Property(Document::class, 'date');
		/** @noinspection PhpUnhandledExceptionInspection constant property */
		$number = new Reflection_Property(Document::class, 'number');

		$date->final_class = $number->final_class = Order::class;
		/** @noinspection PhpUnhandledExceptionInspection constant class name */
		static::assertEquals(
			$this->properties($date, $number),
			(new Reflection_Class(Order::class))->getProperties([T_EXTENDS, T_USE])
		);
	}

}
