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
	 * @var multitype:object
	 */
	protected $collection;

	//----------------------------------------------------------------------------------- $properties
	/**
	 * @var multitype:string
	 */
	protected $properties;

	//------------------------------------------------------------------------------------- $property
	/**
	 * @var Reflection_Property
	 */
	protected $property;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param Reflection_Property $property
	 * @param multitype:object $collection
	 */
	public function __construct(Reflection_Property $property, $collection)
	{
		$this->property = $property;
		$this->collection = $collection;
		$this->class_name = Namespaces::fullClassName(Type::isMultiple($this->property->getType()));
		$class = Reflection_Class::getInstanceOf($this->class_name);
		$this->properties = $class->getAnnotation("representative")->value;
	}

	//----------------------------------------------------------------------------------------- build
	/**
	 * @return string
	 */
	public function build()
	{
		$table = new Html_Table();
		$table->setHead($this->buildHead());
		$table->setBody($this->buildBody());
		return $table;
	}

	//------------------------------------------------------------------------------------- buildCell
	protected function buildCell($object, $property_name)
	{
		return new Html_Table_Standard_Cell($object->$property_name);
	}

	//------------------------------------------------------------------------------------- buildBody
	protected function buildBody()
	{
		$body = new Html_Table_Body();
		foreach ($this->collection as $object) {
			$body->addRow($this->buildRow($object));
		}
		return $body;
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
			$row->addCell(new Html_Table_Header_Cell(
				Loc::tr(Names::propertyToDisplay($property_name), $this->class_name)
			));
		}
		$head->addRow($row);
		return $head;
	}

	//-------------------------------------------------------------------------------------- buildRow
	/**
	 * @param object $object
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
