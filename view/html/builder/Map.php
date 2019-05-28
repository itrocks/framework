<?php
namespace ITRocks\Framework\View\Html\Builder;

use ITRocks\Framework\Controller\Target;
use ITRocks\Framework\Mapper;
use ITRocks\Framework\Reflection\Annotation\Class_\Representative_Annotation;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\View;
use ITRocks\Framework\View\Html\Dom\Anchor;
use ITRocks\Framework\View\Html\Dom\List_\Item;
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
		$list = new Unordered();
		$list->addClass('auto_width');
		$list->addClass('map');
		foreach ($this->buildBody() as $line) {
			$list->addItem($line);
		}
		return $list;
	}

	//------------------------------------------------------------------------------------- buildBody
	/**
	 * @return Item[]
	 */
	protected function buildBody()
	{
		$body = [];
		foreach ($this->map as $object) {
			$body[] = $this->buildCell($object);
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

}
