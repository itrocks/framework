<?php
namespace SAF\Framework;

class Html_Builder_Collection_Edit extends Html_Builder_Collection
{

	//------------------------------------------------------------------------------------- $template
	/**
	 * @var Html_Edit_Template
	 */
	private $template = null;

	//------------------------------------------------------------------------------------- buildBody
	protected function buildBody()
	{
		$body = parent::buildBody();
		$row = $this->buildRow(Builder::create($this->class_name));
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
		))->setTemplate($this->template)->build();
		if ($property_name == reset($this->properties)) {
			$property_builder = new Html_Builder_Property_Edit();
			$property_builder->setTemplate($this->template);
			$id_input = new Html_Input(
				$this->property->name . "[id][" . $property_builder->nextCounter("id[]") . "]",
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
			$row->addCell(new Html_Table_Header_Cell());
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

	//----------------------------------------------------------------------------------- setTemplate
	/**
	 * @param $template Html_Edit_Template
	 * @return Html_Builder_Type_Edit
	 */
	public function setTemplate(Html_Edit_Template $template)
	{
		$this->template = $template;
		return $this;
	}

}
