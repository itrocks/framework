<?php
namespace SAF\Framework;

class Html_Builder_Collection_Edit extends Html_Builder_Collection
{

	//------------------------------------------------------------------------------------- buildCell
	protected function buildCell($object, $property_name)
	{
		$property = Reflection_Class::getInstanceOf(get_class($object))->getProperty($property_name);
		$input = (new Html_Builder_Property_Edit(
			$property, $object->$property_name, $this->property->name . "[]"
		))->build();
		return new Html_Table_Standard_Cell($input);
	}

}
