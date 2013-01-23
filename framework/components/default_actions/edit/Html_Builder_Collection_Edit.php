<?php
namespace SAF\Framework;

class Html_Builder_Collection_Edit extends Html_Builder_Collection
{

	//------------------------------------------------------------------------------------- buildBody
	protected function buildBody()
	{
		$body = parent::buildBody();
		$row = $this->buildRow(Object_Builder::current()->newInstance($this->class_name));
		$row->addClass("new");
		$body->addRow($row);
		return $body;
	}

	//------------------------------------------------------------------------------------- buildCell
	protected function buildCell($object, $property_name)
	{
		$property = Reflection_Property::getInstanceOf($object, $property_name);
		$value = (new Reflection_Property_View($property))->getFormattedValue($object);
		$input = (new Html_Builder_Property_Edit(
			$property, $value, $this->property->name . "[]"
		))->build();
		if ($property_name == reset($this->properties)) {
			$id_input = new Html_Input(
				$this->property->name . "[id][]",
				isset($object->id) ? $object->id : null
			);
			$id_input->setAttribute("type", "hidden");
			$input = $id_input . $input;
		}
		return new Html_Table_Standard_Cell($input);
	}

	//------------------------------------------------------------------------------------- buildHead
	protected function buildHead()
	{
		$head = parent::buildHead();
		foreach ($head->rows as $row) {
			$row->addCell(new Html_Table_Standard_Cell(""));
		}
		return $head;
	}

	//-------------------------------------------------------------------------------------- buildRow
	protected function buildRow($object)
	{
		$row = parent::buildRow($object);
		$cell = new Html_Table_Standard_Cell("-");
		$cell->setAttribute("title", "|remove line|");
		$cell->addClass("minus");
		$row->addCell($cell);
		return $row;
	}

}
