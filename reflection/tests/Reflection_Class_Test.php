<?php
namespace ITRocks\Framework\Reflection\Tests;

use Exception;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Tests\Objects\Document;
use ITRocks\Framework\Tests\Objects\Order;
use ITRocks\Framework\Tests\Test;

/**
 * Reflection class tests
 */
class Reflection_Class_Test extends Test
{

	//---------------------------------------------------------------------------------- INACCESSIBLE
	const INACCESSIBLE = 'inaccessible';

	//------------------------------------------------------------------------------------ properties
	/**
	 * @param $date   Reflection_Property
	 * @param $number Reflection_Property
	 * @return Reflection_Property[]
	 */
	private function properties(Reflection_Property $date, Reflection_Property $number) : array
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
	private function shouldBeInaccessible(string $method, Reflection_Class $class, Order $test_order)
		: void
	{
		$check = [];
		foreach ($class->getProperties([T_EXTENDS, T_USE]) as $property) {
			$property_name = $property->name;
			try {
				$check[$property_name] = $test_order->$property_name;
			}
			catch (Exception $exception) {
				$check[$property_name] = self::INACCESSIBLE;
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
	 * Test access properties
	 *
	 * accessProperties() was removed, so it now testes differences between getValue() and ->property
	 * Disabled as it will not work (look into Properties.php for pending $accessible implementation)
	 */
	private function testAccessProperties() : void
	{
		$class                    = new Reflection_Class(Order::class);
		$today                    = date('Y-m-d');
		$test_order               = new Order($today, 'CDE001');
		$test_order->has_workflow = true;

		// all properties should not be accessible from an order
		$this->shouldBeInaccessible(__METHOD__ . '.1 (getAllProperties)', $class, $test_order);

		// does access properties return properties list ?
		$date   = new Reflection_Property(Document::class, 'date');
		$number = new Reflection_Property(Document::class, 'number');

		$date->final_class = $number->final_class = Order::class;
		static::assertEquals(
			$this->properties($date, $number),
			$properties = $class->getProperties(),
			__METHOD__ . '2 (accessProperties)'
		);

		// are properties now accessible from an order ?
		$check = [];
		foreach ($properties as $property) {
			try {
				$check[$property->name] = $property->getValue($test_order);
			}
			catch (Exception) {
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
		$this->shouldBeInaccessible(__METHOD__ . '.4 (accessPropertiesDone)', $class, $test_order);
	}

	//---------------------------------------------------------------------- testAccessPropertiesDone
	/**
	 * Test access properties done
	 *
	 * accessProperties() was removed, so it now testes differences between getValue() and ->property
	 * Disabled as it will not work (look into Properties.php for pending $accessible implementation)
	 */
	private function testAccessPropertiesDone() : void
	{
		$class                    = new Reflection_Class(Order::class);
		$test_order               = new Order(date('Y-m-d'), 'CDE001');
		$test_order->has_workflow = true;

		$this->shouldBeInaccessible(__METHOD__, $class, $test_order);
	}

	//-------------------------------------------------------------------------- testGetAllProperties
	/**
	 * Test get all properties
	 */
	public function testGetAllProperties() : void
	{
		$date   = new Reflection_Property(Document::class, 'date');
		$number = new Reflection_Property(Document::class, 'number');

		$date->final_class = $number->final_class = Order::class;
		// use array_map as assertEquals gives private property values for actual, not for expected.
		static::assertEquals(
			array_map(
				function(object $object) : array { return get_object_vars($object); },
				$this->properties($date, $number)
			),
			array_map(
				function(object $object) : array { return get_object_vars($object); },
				(new Reflection_Class(Order::class))->getProperties()
			)
		);
	}

}
