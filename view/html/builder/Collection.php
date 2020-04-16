<?php
namespace ITRocks\Framework\View\Html\Builder;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Target;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Feature\Edit\Html_Builder_Property;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Mapper;
use ITRocks\Framework\Reflection\Annotation;
use ITRocks\Framework\Reflection\Annotation\Class_\Link_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Alias_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Foreign_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Integrated_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Representative_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\User_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Widget_Annotation;
use ITRocks\Framework\Reflection\Annotation\Sets\Replaces_Annotations;
use ITRocks\Framework\Reflection\Integrated_Properties;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Reflection\Reflection_Property_Value;
use ITRocks\Framework\Reflection\Reflection_Property_View;
use ITRocks\Framework\Tools\Can_Be_Empty;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\Tools\Stringable;
use ITRocks\Framework\View;
use ITRocks\Framework\View\Html\Dom\Anchor;
use ITRocks\Framework\View\Html\Dom\List_;
use ITRocks\Framework\View\Html\Dom\List_\Item;
use ITRocks\Framework\View\Html\Dom\List_\Ordered;
use ITRocks\Framework\View\Html\Dom\List_\Unordered;
use ITRocks\Framework\View\Html\Template;

/**
 * Takes a collection of objects and build an HTML output containing their data
 */
class Collection
{

	//------------------------------------------------------------------------------- HIDE_EMPTY_TEST
	const HIDE_EMPTY_TEST = true;

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

	//------------------------------------------------------------------------------------ $has_value
	/**
	 * This is the list of properties with @user hide_empty that have a value on at least 1 line
	 *
	 * @var boolean[] key is the name of the property, value is always true
	 */
	public $has_value;

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

	//---------------------------------------------------------------------------- $property_displays
	/**
	 * @var string[]
	 */
	public $property_displays;

	//----------------------------------------------------------------------------------------- $sort
	/**
	 * @var boolean
	 */
	public $sort = true;

	//------------------------------------------------------------------------------------- $template
	/**
	 * @var Template
	 */
	protected $template = null;

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
		$this->properties = $this->expandProperties($this->getProperties());
	}

	//----------------------------------------------------------------------------------------- build
	/**
	 * @return Unordered
	 */
	public function build()
	{
		if ($this->sort) {
			(new Mapper\Collection($this->collection))->sort();
		}
		$list = new Unordered();
		$list->addClass('auto_width');
		$list->addClass('collection');
		$header = $this->buildHeader();
		if (!($header instanceof Item)) {
			$header = new Item($header);
		}
		$header->addClass('header');
		$list->addItem($header);
		foreach ($this->buildBody() as $line) {
			if (!($line instanceof Item)) {
				$value = $line->removeData('id');
				$line  = new Item($line);
				if (isset($value)) {
					$line->setData('id', $value);
				}
			}
			$line->addClass('data');
			$list->addItem($line);
		}
		return $list;
	}

	//------------------------------------------------------------------------------------- buildBody
	/**
	 * @return Item[]|List_[][]
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
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $object        object
	 * @param $property      Reflection_Property
	 * @param $property_path string
	 * @return Item
	 */
	protected function buildCell($object, Reflection_Property $property, $property_path = null)
	{
		/** @noinspection PhpUnhandledExceptionInspection valid $object-$property couple */
		$property_value = strpos($property_path, DOT)
			? new Reflection_Property_Value($object, $property_path, $object)
			: null;
		/** @noinspection PhpUnhandledExceptionInspection valid $object-$property couple */
		$value = ($property_value ?: $property)->getValue($object);
		$type  = $property->getType();
		if (
			is_object($value)
			&& !($value instanceof Stringable)
			&& $type->isSingleClass()
			&& $type->asReflectionClass()->getAnnotation('business')->value
			&& Dao::getObjectIdentifier($value)
		) {
			$anchor = new Anchor(View::link($value), strval($value));
			$anchor->setAttribute('target', Target::MAIN);
			$value = strval($anchor);
		}
		elseif (
			($builder = Widget_Annotation::of($property)->value)
			&& is_a($builder, Property::class, true)
		) {
			if (!$property_value) {
				/** @noinspection PhpUnhandledExceptionInspection from valid property */
				$property_value = new Reflection_Property_Value($object, $property_path, $object);
			}
			/** @noinspection PhpUnhandledExceptionInspection $builder and $property are valid */
			/** @var $builder Property */
			$builder = Builder::create($builder, [$property_value, $value, $this->template]);
			$builder->parameters[Feature::F_EDIT] = Feature::F_EDIT;
			$value = $builder->buildHtml();
			if ($builder instanceof Value_Widget) {
				$value = (new Html_Builder_Property($property_value, $value))
					->setTemplate($this->template)
					->build();
			}
		}
		else {
			$value = $property_value
				? (new Reflection_Property_View($property_value))->getFormattedValue($object)
				: (new Reflection_Property_View($property))->getFormattedValue($object);
		}
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
		$hide_empty_test = !($this->has_value[$property->path] ?? !static::HIDE_EMPTY_TEST);
		if (!$property->isVisible($hide_empty_test)) {
			$cell->addClass('hidden');
			$cell->setStyle('display', 'none');
		}
		$cell->addClass($type->asString());
		$cell->setData(
			'display',
			$this->property_displays[$property->path]
			?? (
				$this->property_displays[$property->path] = Loc::tr(
					Names::propertyToDisplay(Alias_Annotation::of($property)->value), $this->class_name
				)
			)
		);
		$cell->setData('property', $property->path);
		$cell->setData('title',    Loc::tr($property->path));
		return $cell;
	}

	//----------------------------------------------------------------------------------- buildHeader
	/**
	 * @return Ordered
	 */
	protected function buildHeader()
	{
		$header = new Ordered();
		foreach ($this->properties as $property) {
			$type = $property->getType();
			if (
				!$type->isMultiple()
				|| ($type->getElementTypeAsString() != $property->getFinalClass()->name)
			) {
				$cell = new Item(
					Loc::tr(
						Names::propertyToDisplay(Alias_Annotation::of($property)->value),
						$this->class_name
					)
				);
				$hide_empty_test = (
					static::HIDE_EMPTY_TEST
					&& User_Annotation::of($property)->has(User_Annotation::HIDE_EMPTY)
				)
					? !($this->has_value[$property->path] = $this->propertyHasValue($property))
					: static::HIDE_EMPTY_TEST;
				if (!$property->isVisible($hide_empty_test)) {
					$cell->addClass('hidden');
					$cell->setStyle('display', 'none');
				}
				$cell->addClass($type->asString());
				if ($property->getAnnotation('no_autowidth')->value) {
					$cell->addClass('no-autowidth');
				}
				$cell->setData('property', $property->path);
				$cell->setData('title',    Loc::tr($property->path));
				$header->addItem($cell);
			}
		}
		return $header;
	}

	//-------------------------------------------------------------------------------------- buildRow
	/**
	 * @param $object object
	 * @return Ordered
	 */
	protected function buildRow($object)
	{
		$row = new Ordered();
		foreach ($this->properties as $property_path => $property) {
			if (
				!$property->getType()->isMultiple()
				|| ($property->getType()->getElementTypeAsString() != get_class($object))
			) {
				$row->addItem($this->buildCell($object, $property, $property_path));
			}
		}
		$row->setData('id', Dao::getObjectIdentifier($object));
		return $row;
	}

	//------------------------------------------------------------------------------ expandProperties
	/**
	 * @param $properties Reflection_Property[]
	 * @return Reflection_Property[]
	 */
	protected function expandProperties(array $properties)
	{
		$expand_properties = [];
		foreach ($properties as $property_path => $property) {
			if (($integrated = Integrated_Annotation::of($property))->value) {
				$expand_properties = array_merge(
					$expand_properties,
					(new Integrated_Properties())->expandUsingClassName($property->class)
				);
			}
			else {
				$expand_properties[$property_path] = $property;
			}
		}
		return $expand_properties;
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

	//------------------------------------------------------------------------------ propertyHasValue
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $property Reflection_Property
	 * @return boolean
	 */
	protected function propertyHasValue(Reflection_Property $property)
	{
		foreach ($this->collection as $object) {
			/** @noinspection PhpUnhandledExceptionInspection */
			if (
				($value = $property->getValue($object))
				&& ((!$value instanceof Can_Be_Empty) || !$value->isEmpty())
			) {
				return true;
			}
		}
		return false;
	}

	//----------------------------------------------------------------------------------- setTemplate
	/**
	 * @param $template Template
	 * @return static
	 */
	public function setTemplate(Template $template)
	{
		$this->template = $template;
		return $this;
	}

}
