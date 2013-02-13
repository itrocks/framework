<?php
namespace SAF\Framework;

class Html_Builder_Collection
{

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * @var string
	 */
	protected $class_name;

	//----------------------------------------------------------------------------------- $collection
	/**
	 * @var object[]
	 */
	protected $collection;

	//----------------------------------------------------------------------------------- $properties
	/**
	 * @var string[]
	 */
	protected $properties;

	//------------------------------------------------------------------------------------- $property
	/**
	 * @var Reflection_Property
	 */
	protected $property;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $property   Reflection_Property
	 * @param $collection object[]
	 */
	public function __construct(Reflection_Property $property, $collection)
	{
		$this->property = $property;
		$this->collection = $collection;
		$this->class_name = $this->property->getType()->getElementTypeAsString();
		$class = Reflection_Class::getInstanceOf($this->class_name);
		$this->properties = $class->getListAnnotation("representative")->values();
	}

	//----------------------------------------------------------------------------------------- build
	/**
	 * @return Html_Table
	 */
	public function build()
	{
		$table = new Html_Table();
		$table->addClass("collection");
		$table->setHead($this->buildHead());
		$table->setBody($this->buildBody());
		return $table;
	}

	//------------------------------------------------------------------------------------- buildBody
	/**
	 * @return Html_Table_Body
	 */
	protected function buildBody()
	{
		$body = new Html_Table_Body();
		foreach ($this->collection as $object) {
			$body->addRow($this->buildRow($object));
		}
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
		$property = Reflection_Property::getInstanceOf($object, $property_name);
		$cell = new Html_Table_Standard_Cell(
			(new Reflection_Property_View($property))->getFormattedValue($object)
		);
		$type = $property->getType();
		if ($type->isMultiple()) {
			$cell->addClass("multiple");
		}
		$cell->addClass($type->asString());
		return $cell;
	}

	//------------------------------------------------------------------------------------- buildHead
	/**
	 * @return Html_Table_Head
	 */
	protected function buildHead()
	{
		$head = new Html_Table_Head();
		$row = new Html_Table_Row();
		foreach ($this->properties as $property_name) {
			$cell = new Html_Table_Header_Cell(
				Loc::tr(Names::propertyToDisplay($property_name), $this->class_name)
			);
			$cell->addClass("trashable");
			$row->addCell($cell);
		}
		$head->addRow($row);
		return $head;
	}

	//-------------------------------------------------------------------------------------- buildRow
	/**
	 * @param $object object
	 * @return Html_Table_Row
	 */
	protected function buildRow($object)
	{
		$row = new Html_Table_Row();
		foreach ($this->properties as $property_name) {
			$row->addCell($this->buildCell($object, $property_name));
		}
		return $row;
	}

}
