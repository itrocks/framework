<?php
namespace SAF\Framework;

/**
 * Takes a collection of objects and build an HTML output containing their data
 */
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
		$this->properties = $this->getProperties();
	}

	//----------------------------------------------------------------------------------------- build
	/**
	 * @return Html_Table
	 */
	public function build()
	{
		(new Collection($this->collection))->sort();
		$table = new Html_Table();
		$table->addClass('collection');
		$table->head = $this->buildHead();
		$table->body = $this->buildBody();
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
		$property = new Reflection_Property(get_class($object), $property_name);
		$cell = new Html_Table_Standard_Cell(
			(new Reflection_Property_View($property))->getFormattedValue($object)
		);
		$type = $property->getType();
		if ($type->isMultiple()) {
			$cell->addClass('multiple');
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

	//--------------------------------------------------------------------------------- getProperties
	/**
	 * @return string[]
	 */
	protected function getProperties()
	{
		// gets all properties from collection element class
		$class = new Reflection_Class($this->class_name);
		$properties = $class->getAllProperties();
		// remove linked class properties
		$linked_class = $class->getAnnotation('link')->value;
		if ($linked_class) {
			foreach (
				array_keys((new Reflection_Class($linked_class))->getAllProperties())
				as $property_name
			) {
				unset($properties[$property_name]);
			}
		}
		// remove composite property
		$property_name = $this->property->getAnnotation('foreign')->value;
		if (isset($properties[$property_name])) {
			unset($properties[$property_name]);
		}
		// remove static and user-invisible properties
		foreach ($properties as $property_name => $property) {
			if ($property->isStatic() || ($property->getListAnnotation('user')->has('invisible'))) {
				unset($properties[$property_name]);
			}
		}
		// returns properties names only
		return array_keys($properties);
	}

}
