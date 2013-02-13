<?php
namespace SAF\Framework;

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
		$table->addClass("map");
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
