<?php
namespace SAF\Tests\Tests;

use ReflectionException;
use SAF\Framework\Reflection_Class;
use SAF\Framework\Reflection_Property;
use SAF\Framework\Unit_Tests\Unit_Test;
use SAF\Tests\Order;

/**
 * Reflection tests
 */
class Reflection_Test extends Unit_Test
{

	//----------------------------------------------------------------------- testAccessProperties
	public function testAccessProperties()
	{
		// does access properties return properties list ?
		$class = new Reflection_Class('SAF\Tests\Order');
		$test1 = $this->assume(
			__METHOD__ . ".1",
			$properties = $class->accessProperties(),
			array(
				"date"            => new Reflection_Property('SAF\Tests\Document', "date"),
				"number"          => new Reflection_Property('SAF\Tests\Document', "number"),
				"client"          => new Reflection_Property('SAF\Tests\Order',    "client"),
				"delivery_client" => new Reflection_Property('SAF\Tests\Order',    "delivery_client"),
				"lines"           => new Reflection_Property('SAF\Tests\Order',    "lines"),
				"salesmen"        => new Reflection_Property('SAF\Tests\Order',    "salesmen")
			)
		);
		if ($test1) {
			// are properties now accessible ?
			$check = array();
			$test_order = new Order(date("Y-m-d"), "CDE001");
			foreach ($properties as $property) {
				try {
					$check[$property->name] = $property->getValue($test_order);
				}
				catch (ReflectionException $e) {
					$check[$property->name] = null;
				}
			}
			$this->assume(
				__METHOD__ . ".2",
				$check,
				array("date" => date("Y-m-d"), "number" => "CDE001", "client" => null, "lines" => null)
			);
		}
	}

	//------------------------------------------------------------------- testAccessPropertiesDone
	public function testAccessPropertiesDone()
	{
		$test_order = new Order(date("Y-m-d"), "CDE001");
		$class = new Reflection_Class('SAF\Tests\Order');
		$properties = $class->accessProperties();
		$properties = $class->getAllProperties();
		$check = array();
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
			array(
				"date" => null, "number" => null, "client" => null, "delivery_client" => null,
				"lines" => null, "salesmen" => null
			)
		);
	}

	//-------------------------------------------------------------------------- testGetAllProperties
	public function testGetAllProperties()
	{
		$this->assume(
			__METHOD__,
			(new Reflection_Class('SAF\Tests\Order'))->getAllProperties(),
			array(
				"date"            => new Reflection_Property('SAF\Tests\Document', "date"),
				"number"          => new Reflection_Property('SAF\Tests\Document', "number"),
				"client"          => new Reflection_Property('SAF\Tests\Order',    "client"),
				"delivery_client" => new Reflection_Property('SAF\Tests\Order',    "delivery_client"),
				"lines"           => new Reflection_Property('SAF\Tests\Order',    "lines"),
				"salesmen"        => new Reflection_Property('SAF\Tests\Order',    "salesmen")
			)
		);
	}

}
