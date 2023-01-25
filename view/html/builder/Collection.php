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
use ITRocks\Framework\Reflection\Annotation\Property\Conditions_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Foreign_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Integrated_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Representative_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\User_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Widget_Annotation;
use ITRocks\Framework\Reflection\Annotation\Sets\Replaces_Annotations;
use ITRocks\Framework\Reflection\Integrated_Properties;
use ITRocks\Framework\Reflection\Link_Class;
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
	public string $class_name;

	//----------------------------------------------------------------------------------- $collection
	/**
	 * @var object[]
	 */
	public array $collection;

	//------------------------------------------------------------------------------------ $has_value
	/**
	 * This is the list of properties with @user hide_empty that have a value on at least 1 line
	 *
	 * @var boolean[] key is the name of the property, value is always true
	 */
	public array $has_value;

	//----------------------------------------------------------------------------------- $properties
	/**
	 * @var Reflection_Property[]
	 */
	protected array $properties;

	//------------------------------------------------------------------------------------- $property
	/**
	 * @var Reflection_Property
	 */
	public Reflection_Property $property;

	//---------------------------------------------------------------------------- $property_displays
	/**
	 * @var string[]
	 */
	public array $property_displays;

	//----------------------------------------------------------------------------------------- $sort
	/**
	 * @var boolean
	 */
	public bool $sort = true;

	//------------------------------------------------------------------------------------- $template
	/**
	 * @var ?Template
	 */
	public ?Template $template = null;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $property        Reflection_Property
	 * @param $collection      object[]
	 * @param $link_properties boolean
	 *   Linked class properties are hidden by default : only the link property is shown
	 *   If set to true, the link property will be hidden and the class properties shown
	 */
	public function __construct(
		Reflection_Property $property, array $collection, bool $link_properties = false
	) {
		$this->property   = $property;
		$this->collection = $collection;
		$this->class_name = $this->property->getType()->getElementTypeAsString();
		$this->properties = $this->expandProperties($this->getProperties($link_properties));
	}

	//----------------------------------------------------------------------------------------- build
	/**
	 * @return Unordered
	 */
	public function build() : Unordered
	{
		if ($this->sort) {
			(new Mapper\Collection($this->collection))->sort();
		}
		$list = new Unordered();
		$list->addClass('auto_width');
		$list->addClass('collection');
		$header = new Item($this->buildHeader());
		$header->addClass('head');
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
	protected function buildBody() : array
	{
		$body = [];
		foreach ($this->collection as $object) {
			if (isset($object->{CONFIDENTIAL})) {
				continue;
			}
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
	protected function buildCell(object $object, Reflection_Property $property, string $property_path)
		: Item
	{
		/** @noinspection PhpUnhandledExceptionInspection valid $object-$property couple */
		$property_value = new Reflection_Property_Value($object, $property_path, $object, false, true);
		$type           = $property->getType();
		$value          = $property_value->value();
		if (
			is_object($value)
			&& !($value instanceof Stringable)
			&& $type->isSingleClass()
			&& (
				($class = $type->asReflectionClass())->getAnnotation('business')->value
				|| $class->getAnnotation('stored')->value
			)
			&& Dao::getObjectIdentifier($value, 'id')
		) {
			$anchor = new Anchor(View::link($value), strval($value));
			$anchor->setAttribute('target', Target::MAIN);
			$value = strval($anchor);
		}
		elseif (
			($builder = Widget_Annotation::of($property)->value)
			&& is_a($builder, Property::class, true)
		) {
			/** @noinspection PhpParamsInspection Inspector bug : $builder is a string */
			/** @noinspection PhpUnhandledExceptionInspection $builder and $property are valid */
			/** @var $builder Property */
			$builder                              = Builder::create($builder, [$property_value, $value, $this->template]);
			$builder->parameters[Feature::F_EDIT] = Feature::F_EDIT;
			$value                                = $builder->buildHtml();
			if ($builder instanceof Value_Widget) {
				$value = (new Html_Builder_Property($property_value, $value))
					->setTemplate($this->template)
					->build();
			}
		}
		else {
			$value = (new Reflection_Property_View($property_value))->getFormattedValue($object);
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

		if ($value instanceof Dao\File) {
			$value = (new File($value))->build();
		}
		if (
			(is_string($value) || (is_object($value) && method_exists($value, '__toString')))
			&& str_contains($value, '|')
		) {
			$value = str_replace('|', '&#124;', $value);
		}
		if (
			isset($value)
			&& !is_array($value)
			&& strlen($value)
			&& !Conditions_Annotation::of($property)->applyTo($object)
		) {
			$value = null;
		}
		$cell = new Item($value);
		$hide_empty_test = !($this->has_value[$property->path] ?? !static::HIDE_EMPTY_TEST);
		if (!$property->isVisible($hide_empty_test)) {
			$cell->addClass('hidden');
			$cell->setStyle('display', 'none');
		}
		$cell->addClass($type->asString());
		if ($property->getAnnotation('multiline')->value) {
			$cell->addClass('multiline');
		}
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
		if ($component_object_html = $property->isComponentObjectHtml()) {
			$cell->addClass($component_object_html);
		}
		return $cell;
	}

	//----------------------------------------------------------------------------------- buildHeader
	/**
	 * @return Ordered
	 */
	protected function buildHeader() : Ordered
	{
		$header = new Ordered();
		foreach ($this->properties as $property) {
			$type = $property->getType();
			if (
				!$type->isMultiple()
				|| ($type->getElementTypeAsString() !== $property->getFinalClass()->name)
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
				if ($editor = $property->getAnnotation('editor')->value) {
					$cell->addClass(lParse($editor, SP));
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
	protected function buildRow(object $object) : Ordered
	{
		$row = new Ordered();
		foreach ($this->properties as $property_path => $property) {
			if (
				!$property->getType()->isMultiple()
				|| ($property->getType()->getElementTypeAsString() !== get_class($object))
			) {
				$row->addItem($this->buildCell($object, $property, $property_path));
			}
		}
		$row->setData('id', Dao::getObjectIdentifier($object, 'id'));
		return $row;
	}

	//------------------------------------------------------------------------------ expandProperties
	/**
	 * @param $properties Reflection_Property[]
	 * @return Reflection_Property[]
	 */
	protected function expandProperties(array $properties) : array
	{
		$expand_properties = [];
		foreach ($properties as $property_path => $property) {
			if (Integrated_Annotation::of($property)->value) {
				if (!isset($expand)) {
					$expand = (new Integrated_Properties)->expandUsingClassName($property->class);
				}
				foreach ($expand as $expand_property_path => $expand_property) {
					if (
						str_starts_with($expand_property_path, $property_path . DOT)
						&& $this->isPropertyVisible($expand_property)
					) {
						$expand_properties[$expand_property_path] = $expand_property;
					}
				}
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
	 * @param $link_properties boolean
	 * @return Reflection_Property[]
	 */
	protected function getProperties(bool $link_properties) : array
	{
		$property_display_order = $this->property->getListAnnotations('display_order');
		/** @noinspection PhpUnhandledExceptionInspection class name must be valid */
		$class          = new Reflection_Class($this->class_name);
		$representative = Representative_Annotation::of($this->property);
		/** @var $properties Reflection_Property[] */
		$properties = $representative->getProperties();
		if (!$properties) {
			// gets all properties from collection element class
			$properties = $class->getProperties([T_EXTENDS, T_USE]);
			// remove replaced properties
			$properties = Replaces_Annotations::removeReplacedProperties($properties);
			// remove linked class properties
			$linked_class = Link_Annotation::of($class)->value;
			if ($linked_class) {
				if ($link_properties) {
					// remove link property
					/** @noinspection PhpUnhandledExceptionInspection must be valid */
					$link_property = (new Link_Class($this->class_name))->getLinkProperty();
					unset($properties[$link_property->name]);
				}
				else {
					/** @noinspection PhpUnhandledExceptionInspection link class comes from valid class */
					foreach (
						array_keys((new Reflection_Class($linked_class))->getProperties([T_EXTENDS, T_USE]))
						as $property_name
					) {
						unset($properties[$property_name]);
					}
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
			$properties = $class->sortProperties($properties, $property_display_order);
		}
		elseif ($property_display_order) {
			$properties = $class->sortProperties($properties, $property_display_order);
		}

		// returns properties
		return $properties;
	}

	//----------------------------------------------------------------------------- isPropertyVisible
	/**
	 * @param $property Reflection_Property
	 * @return boolean
	 */
	protected function isPropertyVisible(Reflection_Property $property) : bool
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
	protected function propertyHasValue(Reflection_Property $property) : bool
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

}
