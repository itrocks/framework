<?php
namespace SAF\Framework;

/**
 * Takes a map of objects and builds HTML code using their data
 */
class Html_Builder_Map
{

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * @var string
	 */
	protected $class_name;

	//------------------------------------------------------------------------------------------ $map
	/**
	 * @var object[]
	 */
	protected $map;

	//----------------------------------------------------------------------------------- $properties
	/**
	 * @var Reflection_Property[]
	 */
	protected $properties;

	//------------------------------------------------------------------------------------- $property
	/**
	 * @var Reflection_Property
	 */
	protected $property;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $property Reflection_Property
	 * @param $map      object[]
	 */
	public function __construct(Reflection_Property $property, $map)
	{
		$this->property = $property;
		$this->map = $map;
		$this->class_name = $this->property->getType()->getElementTypeAsString();
		$class = new Reflection_Class($this->class_name);
		/** @var $representative Class_Representative_Annotation */
		$representative = $class->getListAnnotation('representative');
		$this->properties = $representative->getProperties();
	}

	//----------------------------------------------------------------------------------------- build
	/**
	 * @return Html_Table
	 */
	public function build()
	{
		(new Map($this->map))->sort();
		$table = new Html_Table();
		$table->addClass('map');
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
		foreach ($this->map as $object) {
			$body->addRow($this->buildRow($object));
		}
		return $body;
	}

	//------------------------------------------------------------------------------------- buildCell
	/**
	 * @param $object object
	 * @return Html_Table_Standard_Cell
	 */
	protected function buildCell($object)
	{
		return new Html_Table_Standard_Cell(strval($object));
	}

	//------------------------------------------------------------------------------------- buildHead
	/**
	 * @return Html_Table_Head
	 */
	protected function buildHead()
	{
		$head = new Html_Table_Head();
		$row = new Html_Table_Row();
		foreach ($this->properties as $property) {
			$cell = new Html_Table_Header_Cell(Loc::tr(
				Names::propertyToDisplay($property->getAnnotation('alias')->value),
				$this->class_name
			));
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
		$row->addCell($this->buildCell($object));
		return $row;
	}

}
