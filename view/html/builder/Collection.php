<?php
namespace ITRocks\Framework\View\Html\Builder;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Mapper;
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
use ITRocks\Framework\View\Html\Dom\Table;
use ITRocks\Framework\View\Html\Dom\Table\Body;
use ITRocks\Framework\View\Html\Dom\Table\Head;
use ITRocks\Framework\View\Html\Dom\Table\Header_Cell;
use ITRocks\Framework\View\Html\Dom\Table\Row;
use ITRocks\Framework\View\Html\Dom\Table\Standard_Cell;

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
	 * @return Table
	 */
	public function build()
	{
		(new Mapper\Collection($this->collection))->sort();
		$table = new Table();
		$table->addClass('collection');
		$table->head = $this->buildHead();
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
		foreach ($this->collection as $object) {
			$body->addRow($this->buildRow($object));
		}
		return $body;
	}

	//------------------------------------------------------------------------------------- buildCell
	/**
	 * @param $object   object
	 * @param $property Reflection_Property
	 * @return Standard_Cell
	 */
	protected function buildCell($object, Reflection_Property $property)
	{
		$value = (new Reflection_Property_View($property))->getFormattedValue($object);
		$cell = ($value instanceof Dao\File)
			? new Standard_Cell((new File($value))->build())
			: new Standard_Cell($value);
		$type = $property->getType();
		if ($type->isMultiple()) {
			$cell->addClass('multiple');
		}
		$cell->addClass($type->asString());
		return $cell;
	}

	//------------------------------------------------------------------------------------- buildHead
	/**
	 * @return Head
	 */
	protected function buildHead()
	{
		$head = new Head();
		$row  = new Row();
		foreach ($this->properties as $property) {
			if (
				!$property->getType()->isMultiple()
				|| ($property->getType()->getElementTypeAsString() != $property->getFinalClass()->name)
			) {
				$cell = new Header_Cell(
					Loc::tr(
						Names::propertyToDisplay($property->getAnnotation(Alias_Annotation::ANNOTATION)->value),
						$this->class_name
					)
				);
				$row->addCell($cell);
			}
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
		foreach ($this->properties as $property) {
			if (
				!$property->getType()->isMultiple()
				|| ($property->getType()->getElementTypeAsString() != get_class($object))
			) {
				$row->addCell($this->buildCell($object, $property));
			}
		}
		return $row;
	}

	//--------------------------------------------------------------------------------- getProperties
	/**
	 * @return Reflection_Property[]
	 */
	protected function getProperties()
	{
		/** @var $representative Representative_Annotation */
		$representative = Representative_Annotation::of($this->property);
		$properties     = $representative->getProperties();
		if (!$properties) {
			// gets all properties from collection element class
			$class      = new Reflection_Class($this->class_name);
			$properties = $class->getProperties([T_EXTENDS, T_USE]);
			// remove replaced properties
			/** @var $properties Reflection_Property[] */
			$properties = Replaces_Annotations::removeReplacedProperties($properties);
			// remove linked class properties
			$linked_class = Link_Annotation::of($class)->value;
			if ($linked_class) {
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
				if (
					$property->isStatic()
					|| $property->getListAnnotation(User_Annotation::ANNOTATION)->has(
						User_Annotation::INVISIBLE
					)
				) {
					unset($properties[$property_name]);
				}
			}
		}
		// returns properties
		return $properties;
	}

}
