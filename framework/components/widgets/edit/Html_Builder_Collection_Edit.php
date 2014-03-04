<?php
namespace SAF\Framework;

/**
 * Takes a collection of objects and build a HTML edit subform containing their data
 */
class Html_Builder_Collection_Edit extends Html_Builder_Collection
{

	//------------------------------------------------------------------------------------- $template
	/**
	 * @var Html_Edit_Template
	 */
	private $template = null;

	//------------------------------------------------------------------------------------- buildBody
	/**
	 * @return Html_Table_Body
	 */
	protected function buildBody()
	{
		$body = parent::buildBody();
		$row = $this->buildRow(Builder::create($this->class_name));
		$row->addClass('new');
		$body->addRow($row);
		return $body;
	}

	//------------------------------------------------------------------------------------- buildCell
	/**
	 * @param $object        object
	 * @param $property_name string
	 * @return Html_Table_Standard_Cell
	 */
	protected function buildCell($object, $property_name)
	{
		if (!isset($this->template)) {
			$this->template = new Html_Edit_Template();
		}
		$property = new Reflection_Property(get_class($object), $property_name);
		$value = (new Reflection_Property_View($property))->getFormattedValue($object);
		$builder = (new Html_Builder_Property_Edit($property, $value, $this->property->name . '[]'));
		$input = $builder->setTemplate($this->template)->build();
		if ($property_name == reset($this->properties)) {
			$property_builder = new Html_Builder_Property_Edit();
			$property_builder->setTemplate($this->template);
			$id_input = new Html_Input(
				$this->property->name . '[id]['
				. $property_builder->nextCounter($this->property->name . '[id][]')
				. ']',
				isset($object->id) ? $object->id : null
			);
			$id_input->setAttribute('type', 'hidden');
			$input = $id_input . $input;
		}
		return new Html_Table_Standard_Cell($input);
	}

	//------------------------------------------------------------------------------------- buildHead
	/**
	 * @return Html_Table_Head
	 */
	protected function buildHead()
	{
		$head = parent::buildHead();
		foreach ($head->rows as $row) {
			$row->addCell(new Html_Table_Header_Cell());
		}
		return $head;
	}

	//-------------------------------------------------------------------------------------- buildRow
	/**
	 * @param $object object
	 * @return Html_Table_Row
	 */
	protected function buildRow($object)
	{
		$row = parent::buildRow($object);
		$cell = new Html_Table_Standard_Cell('-');
		$cell->setAttribute('title', '|remove line|');
		$cell->addClass('minus');
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
