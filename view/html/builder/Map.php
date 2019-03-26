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
use ITRocks\Framework\View\Html\Dom\List_\Item;
use ITRocks\Framework\View\Html\Dom\List_\Ordered;
use ITRocks\Framework\View\Html\Dom\List_\Unordered;

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

	//---------------------------------------------------------------------------------- $is_abstract
	/**
	 * @var boolean
	 */
	protected $is_abstract;

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
		$class             = new Reflection_Class($this->class_name);
		$representative    = Representative_Annotation::of($class);
		$this->properties  = $representative->getProperties();
		$this->is_abstract = $class->isAbstract();
	}

	//----------------------------------------------------------------------------------------- build
	/**
	 * @return Unordered
	 */
	public function build()
	{
		(new Mapper\Map($this->map))->sort();
		$table = new Unordered();
		$table->addClass('map');
		foreach ($this->buildBody() as $row) {
			$table->addItem($row);
		}
		return $table;
	}

	//------------------------------------------------------------------------------------- buildBody
	/**
	 * @return Item[]
	 */
	protected function buildBody()
	{
		$body = [];
		foreach ($this->map as $object) {
			$body[] = $this->buildRow($object);
		}
		return $body;
	}

	//------------------------------------------------------------------------------------- buildCell
	/**
	 * @param $object object
	 * @return Item
	 */
	protected function buildCell($object)
	{
		$anchor = new Anchor(View::link($object), strval($object));
		$anchor->setAttribute('target', Target::MAIN);
		return new Item($anchor);
	}

	//------------------------------------------------------------------------------------- buildHead
	/**
	 * @return Ordered
	 */
	protected function buildHead()
	{
		$head = new Ordered();
		foreach ($this->properties as $property) {
			$cell = new Item(Loc::tr(
				Names::propertyToDisplay($property->getAnnotation(Alias_Annotation::ANNOTATION)->value),
				$this->class_name
			));
			$head->addItem($cell);
		}
		return $head;
	}

	//-------------------------------------------------------------------------------------- buildRow
	/**
	 * @param $object object
	 * @return Ordered
	 */
	protected function buildRow($object)
	{
		$row = new Ordered();
		$row->addItem($this->buildCell($object));
		return $row;
	}

}
