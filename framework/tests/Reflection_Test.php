<?php
namespace SAF\Framework\Tests;
use SAF\Framework\Reflection_Class;
use SAF\Framework\Reflection_Property;
use ReflectionException;

class Reflection_Test extends Unit_Test
{

	//----------------------------------------------------------------------- testaccessProperties
	public function testAccessProperties()
	{
		// does access properties return properties list ?
		$class = Reflection_Class::getInstanceOf(__NAMESPACE__ . "\\Test_Order");
		$test1 = $this->assume(
			__METHOD__ . ".1",
			$properties = $class->accessProperties(),
			array(
				"date"     => Reflection_Property::getInstanceOf(__NAMESPACE__ . "\\Test_Document", "date"),
				"number"   => Reflection_Property::getInstanceOf(__NAMESPACE__ . "\\Test_Document", "number"),
				"client"   => Reflection_Property::getInstanceOf(__NAMESPACE__ . "\\Test_Order",    "client"),
				"lines"    => Reflection_Property::getInstanceOf(__NAMESPACE__ . "\\Test_Order",    "lines"),
				"salesmen" => Reflection_Property::getInstanceOf(__NAMESPACE__ . "\\Test_Order",    "salesmen")
			)
		);
		if ($test1) {
			// are properties now accessible ?
			$check = array();
			$test_order = new Test_Order(date("Y-m-d"), "CDE001");
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
		$class->accessPropertiesDone();
	}

	//------------------------------------------------------------------- testAccessPropertiesDone
	public function testAccessPropertiesDone()
	{
		$test_order = new Test_Order(date("Y-m-d"), "CDE001");
		$class = Reflection_Class::getInstanceOf(__NAMESPACE__ . "\\Test_Order");
		$properties = $class->accessProperties();
		$class->accessPropertiesDone();
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
			array("date" => null, "number" => null, "client" => null, "lines" => null, "salesmen" => null)
		);
	}

	//-------------------------------------------------------------------------- testGetAllProperties
	public function testGetAllProperties()
	{
		$this->assume(
			__METHOD__,
			Reflection_Class::getInstanceOf(__NAMESPACE__ . "\\Test_Order")->getAllProperties(),
			array(
				"date"     => Reflection_Property::getInstanceOf(__NAMESPACE__ . "\\Test_Document", "date"),
				"number"   => Reflection_Property::getInstanceOf(__NAMESPACE__ . "\\Test_Document", "number"),
				"client"   => Reflection_Property::getInstanceOf(__NAMESPACE__ . "\\Test_Order",    "client"),
				"lines"    => Reflection_Property::getInstanceOf(__NAMESPACE__ . "\\Test_Order",    "lines"),
				"salesmen" => Reflection_Property::getInstanceOf(__NAMESPACE__ . "\\Test_Order",    "salesmen")
			)
		);
	}

}
