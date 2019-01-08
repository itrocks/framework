<?php
namespace ITRocks\Framework\View\Html\Builder;

use ITRocks\Framework\Controller\Target;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Mapper;
use ITRocks\Framework\Reflection\Annotation\Class_\Representative_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Alias_Annotation;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\View;
use ITRocks\Framework\View\Html\Dom\Anchor;
use ITRocks\Framework\View\Html\Dom\Table;
use ITRocks\Framework\View\Html\Dom\Table\Body;
use ITRocks\Framework\View\Html\Dom\Table\Head;
use ITRocks\Framework\View\Html\Dom\Table\Header_Cell;
use ITRocks\Framework\View\Html\Dom\Table\Row;
use ITRocks\Framework\View\Html\Dom\Table\Standard_Cell;

/**
 * Takes a map of objects and builds HTML code using their data
 */
class Map
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
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $property Reflection_Property
	 * @param $map      object[]
	 */
	public function __construct(Reflection_Property $property, array $map)
	{
		$this->property   = $property;
		$this->map        = $map;
		$this->class_name = $this->property->getType()->getElementTypeAsString();
		/** @noinspection PhpUnhandledExceptionInspection class name must be valid */
		$class            = new Reflection_Class($this->class_name);
		$representative   = Representative_Annotation::of($class);
		$this->properties = $representative->getProperties();
	}

	//----------------------------------------------------------------------------------------- build
	/**
	 * @return Table
	 */
	public function build()
	{
		(new Mapper\Map($this->map))->sort();
		$table = new Table();
		$table->addClass('map');
		$table->body = $this->buildBody();
		return $table;
	}

	//------------------------------------------------------------------------------------- buildBody
	/**
	 * @return Body
	 */
	protected function buildBody()
	{
		$body = new Body();
		foreach ($this->map as $object) {
			$body->addRow($this->buildRow($object));
		}
		return $body;
	}

	//------------------------------------------------------------------------------------- buildCell
	/**
	 * @param $object object
	 * @return Standard_Cell
	 */
	protected function buildCell($object)
	{
		$anchor = new Anchor(View::link($object), strval($object));
		$anchor->setAttribute('target', Target::MAIN);
		return new Standard_Cell($anchor);
	}

	//------------------------------------------------------------------------------------- buildHead
	/**
	 * @return Head
	 */
	protected function buildHead()
	{
		$head = new Head();
		$row = new Row();
		foreach ($this->properties as $property) {
			$cell = new Header_Cell(Loc::tr(
				Names::propertyToDisplay($property->getAnnotation(Alias_Annotation::ANNOTATION)->value),
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
	 * @return Row
	 */
	protected function buildRow($object)
	{
		$row = new Row();
		$row->addCell($this->buildCell($object));
		return $row;
	}

}
