<?php
namespace ITRocks\Framework\View\Html\Builder;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Mapper;
use ITRocks\Framework\Reflection\Annotation;
use ITRocks\Framework\Reflection\Annotation\Class_\Link_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Alias_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Foreign_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Representative_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\User_Annotation;
use ITRocks\Framework\Reflection\Annotation\Sets\Replaces_Annotations;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Reflection\Reflection_Property_View;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\View\Html\Dom\List_\Item;
use ITRocks\Framework\View\Html\Dom\List_\Ordered;
use ITRocks\Framework\View\Html\Dom\List_\Unordered;

/**
 * Takes a collection of objects and build an HTML output containing their data
 */
class Collection
{

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * @var string
	 */
	public $class_name;

	//----------------------------------------------------------------------------------- $collection
	/**
	 * @var object[]
	 */
	public $collection;

	//----------------------------------------------------------------------------------- $properties
	/**
	 * @var Reflection_Property[]
	 */
	protected $properties;

	//------------------------------------------------------------------------------------- $property
	/**
	 * @var Reflection_Property
	 */
	public $property;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $property   Reflection_Property
	 * @param $collection object[]
	 */
	public function __construct(Reflection_Property $property, array $collection)
	{
		$this->property   = $property;
		$this->collection = $collection;
		$this->class_name = $this->property->getType()->getElementTypeAsString();
		$this->properties = $this->getProperties();
	}

	//----------------------------------------------------------------------------------------- build
	/**
	 * @return Unordered
	 */
	public function build()
	{
		(new Mapper\Collection($this->collection))->sort();
		$list = new Unordered();
		$list->addClass('collection');
		$list->addItem($this->buildHead());
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
		foreach ($this->collection as $object) {
			$body[] = $this->buildRow($object);
		}
		return $body;
	}

	//------------------------------------------------------------------------------------- buildCell
	/**
	 * @param $object   object
	 * @param $property Reflection_Property
	 * @return Item
	 */
	protected function buildCell($object, Reflection_Property $property)
	{
		$value = (new Reflection_Property_View($property))->getFormattedValue($object);
		if (is_array($value)) {
			$link_annotation = Annotation\Property\Link_Annotation::of($property);
			if ($link_annotation->isCollection()) {
				$value = (new Collection($property, $value))->build();
			}
			elseif ($link_annotation->isMap()) {
				$value = (new Map($property, $value))->build();
			}
		}
		$cell = new Item(($value instanceof Dao\File) ? (new File($value))->build() : $value);
		$type = $property->getType();
		if ($type->isMultiple()) {
			$cell->addClass('multiple');
		}
		if (!$property->isVisible()) {
			$cell->addClass('hidden');
			$cell->setStyle('display', 'none');
		}
		$cell->addClass($type->asString());
		$cell->setData(
			'name',
			Loc::tr(
				Names::propertyToDisplay($property->getAnnotation(Alias_Annotation::ANNOTATION)->value),
				$this->class_name
			)
		);
		return $cell;
	}

	//------------------------------------------------------------------------------------- buildHead
	/**
	 * @return Ordered
	 */
	protected function buildHead()
	{
		$head = new Ordered();
		foreach ($this->properties as $property) {
			if (
				!$property->getType()->isMultiple()
				|| ($property->getType()->getElementTypeAsString() != $property->getFinalClass()->name)
			) {
				$cell = new Item(
					Loc::tr(
						Names::propertyToDisplay($property->getAnnotation(Alias_Annotation::ANNOTATION)->value),
						$this->class_name
					)
				);
				if (!$property->isVisible()) {
					$cell->addClass('hidden');
					$cell->setStyle('display', 'none');
				}
				$head->addItem($cell);
			}
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
		foreach ($this->properties as $property) {
			if (
				!$property->getType()->isMultiple()
				|| ($property->getType()->getElementTypeAsString() != get_class($object))
			) {
				$row->addItem($this->buildCell($object, $property));
			}
		}
		return $row;
	}

	//--------------------------------------------------------------------------------- getProperties
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return Reflection_Property[]
	 */
	protected function getProperties()
	{
		/** @noinspection PhpUnhandledExceptionInspection class name must be valid */
		$class          = new Reflection_Class($this->class_name);
		$representative = Representative_Annotation::of($this->property);
		$properties     = $representative->getProperties();
		if (!$properties) {
			// gets all properties from collection element class
			$properties = $class->getProperties([T_EXTENDS, T_USE]);
			// remove replaced properties
			/** @var $properties Reflection_Property[] */
			$properties = Replaces_Annotations::removeReplacedProperties($properties);
			// remove linked class properties
			$linked_class = Link_Annotation::of($class)->value;
			if ($linked_class) {
				/** @noinspection PhpUnhandledExceptionInspection link class comes from valid class */
				foreach (
					array_keys((new Reflection_Class($linked_class))->getProperties([T_EXTENDS, T_USE]))
					as $property_name
				) {
					unset($properties[$property_name]);
				}
			}
			// remove composite property
			$property_name = Foreign_Annotation::of($this->property)->value;
			if (isset($properties[$property_name])) {
				unset($properties[$property_name]);
			}
			// remove static and user-invisible properties
			foreach ($properties as $property_name => $property) {
				if ($property->isStatic() || !$this->isPropertyVisible($property)) {
					unset($properties[$property_name]);
				}
			}
		}
		// use @display_order to reorder properties
		$properties = $class->sortProperties($properties);

		// returns properties
		return $properties;
	}

	//----------------------------------------------------------------------------- isPropertyVisible
	/**
	 * @param $property Reflection_Property
	 * @return boolean
	 */
	protected function isPropertyVisible(Reflection_Property $property)
	{
		$user_annotation = $property->getListAnnotation(User_Annotation::ANNOTATION);
		return !$user_annotation->has(User_Annotation::HIDE_OUTPUT)
			&& !$user_annotation->has(User_Annotation::INVISIBLE)
			&& !$user_annotation->has(User_Annotation::INVISIBLE_OUTPUT);
	}

}
