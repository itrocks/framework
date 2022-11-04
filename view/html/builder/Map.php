<?php
namespace ITRocks\Framework\View\Html\Builder;

use ITRocks\Framework\Controller\Target;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Mapper;
use ITRocks\Framework\Reflection\Annotation\Class_\Representative_Annotation;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\View;
use ITRocks\Framework\View\Html\Dom\Anchor;
use ITRocks\Framework\View\Html\Dom\Element;
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
	protected string $class_name;

	//---------------------------------------------------------------------------------- $is_abstract
	/**
	 * @var boolean
	 */
	protected bool $is_abstract;

	//------------------------------------------------------------------------------------------ $map
	/**
	 * @var object[]
	 */
	protected array $map;

	//----------------------------------------------------------------------------------- $properties
	/**
	 * @todo is not this dead code ? Look if used on any project, remove it if unused
	 * @var Reflection_Property[]
	 */
	protected array $properties;

	//------------------------------------------------------------------------------------- $property
	/**
	 * @var Reflection_Property
	 */
	protected Reflection_Property $property;

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
	public function build() : Unordered
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
	 * @return Element[]
	 */
	protected function buildBody() : array
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
	 * @return Element
	 */
	protected function buildCell(object $object) : Element
	{
		if ($object instanceof Dao\File) {
			$element = (new File($object, $this->property))->build();
		}
		else {
			$value = strval($object);
			if (str_contains($value, '|')) {
				$value = str_replace('|', '&#124;', $value);
			}
			$element = new Anchor(View::link($object), $value);
			$element->setAttribute('target', Target::MAIN);
		}
		return new Item($element);
	}

}
