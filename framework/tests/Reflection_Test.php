<?php
namespace Framework\Tests;
use \Framework\Class_Fields;
use \Framework\Reflection_Property;
use \ReflectionException;

class Reflection_Test extends Unit_Test
{

	//------------------------------------------------------------------------------------ testFields
	public function testFields()
	{
		$this->assume(
			__METHOD__,
			Class_Fields::fields("Test_Order"),
			array(
				"date"   => Reflection_Property::getInstanceOf("Test_Document", "date"),
				"number" => Reflection_Property::getInstanceOf("Test_Document", "number"),
				"client" => Reflection_Property::getInstanceOf("Test_Order",    "client"),
				"lines"  => Reflection_Property::getInstanceOf("Test_Order",    "lines")
			)
		);
	}

	//------------------------------------------------------------------------------ testAccessFields
	public function testAccessFields()
	{
		// does access fields return fields list ?
		$test1 = $this->assume(
			__METHOD__ . ".1",
			$properties = Class_Fields::accessFields("Test_Order"),
			array(
				"date"   => Reflection_Property::getInstanceOf("Test_Document", "date"),
				"number" => Reflection_Property::getInstanceOf("Test_Document", "number"),
				"client" => Reflection_Property::getInstanceOf("Test_Order",    "client"),
				"lines"  => Reflection_Property::getInstanceOf("Test_Order",    "lines")
			)
		);
		if ($test1) {
			// are properties now accessible ?
			$check = array();
			$test_order = new Test_Order(date("Y-m-d"), "CDE001");
			foreach ($properties as $property) {
				try {
					$check[$property->name] = $property->getValue($test_order);
				} catch (ReflectionException $e) {
					$check[$property->name] = null;
				}
			}
			$this->assume(
				__METHOD__ . ".2",
				$check,
				array("date" => date("Y-m-d"), "number" => "CDE001", "client" => null, "lines" => null)
			);
		}
		Class_Fields::accessFieldsDone("Test_Order");
	}

	//-------------------------------------------------------------------------- testAccessFieldsDone
	public function testAccessFieldsDone()
	{
		$test_order = new Test_Order(date("Y-m-d"), "CDE001");
		$fields = Class_Fields::accessFields("Test_Order");
		Class_Fields::accessFieldsDone("Test_Order");
		foreach ($fields as $property) {
			try {
				$check[$property->name] = $property->getValue($test_order);
			} catch (ReflectionException $e) {
				$check[$property->name] = null;
			}
		}
		$this->assume(
			__METHOD__,
			$check,
			array("date" => null, "number" => null, "client" => null, "lines" => null)
		);
	}

}
