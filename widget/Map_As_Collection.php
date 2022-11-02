<?php
namespace ITRocks\Framework\Widget;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Feature\Edit\Html_Builder_Collection;
use ITRocks\Framework\Feature\Edit\Html_Template;
use ITRocks\Framework\Mapper\Empty_Object;
use ITRocks\Framework\Mapper\Object_Builder_Array;
use ITRocks\Framework\Reflection\Annotation\Property\Widget_Annotation;
use ITRocks\Framework\Reflection\Link_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Traits\Is_Immutable;
use ITRocks\Framework\View\Html\Builder\Collection;
use ITRocks\Framework\View\Html\Builder\Property;

/**
 * This property widget displays a Map like a collection
 */
class Map_As_Collection extends Property
{

	//--------------------------------------------------------------------------- $composite_property
	/**
	 * @var ?Reflection_Property
	 */
	protected ?Reflection_Property $composite_property = null;

	//------------------------------------------------------------------------------------- $pre_path
	/**
	 * @var string
	 */
	public string $pre_path = '';

	//------------------------------------------------------------------------------------- buildHtml
	/**
	 * @return string
	 */
	public function buildHtml()
	{
		// TODO LOW remove this "if" statement, trigger a notice, debug step by step and optimize
		// this "if" patch is here because parseSingleValue() calls this both : we have to build html
		// on first pass only.
		if (is_array($this->value)) {
			// - immutable objects : disconnect elements
			foreach ($this->value as $element) {
				if (isA($element, Is_Immutable::class)) {
					Dao::disconnect($element);
				}
			}
			// - edit
			if ($this->template instanceof Html_Template) {
				$collection = new Html_Builder_Collection(
					$this->property, $this->value, true, $this->pre_path
				);
				$collection->template = $this->template;
			}
			// - output
			else {
				$collection = new Collection($this->property, $this->value, true);
			}
			$collection->sort = Widget_Annotation::of($this->property)->option(
				Widget_Annotation::SORT, true
			);
			// build
			return $collection->build();
		}
		elseif (isA($this->value, Is_Immutable::class)) {
			Dao::disconnect($this->value);
		}
		return $this->value;
	}

	//------------------------------------------------------------------------------------ buildValue
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $object        object
	 * @param $null_if_empty boolean
	 * @return mixed
	 */
	public function buildValue($object, $null_if_empty)
	{
		$builder                            = new Object_Builder_Array();
		$class_name                         = $this->property->getType()->getElementTypeAsString();
		$builder->null_if_empty_sub_objects = true;
		/** @noinspection PhpUnhandledExceptionInspection */
		$old_value  = $this->property->getValue($object);
		$collection = $builder->buildCollection($class_name, $old_value, $this->value, $null_if_empty);

		// Remove empty objects from collection to avoid control on null value
		foreach ($collection as $key => $element) {
			if (Empty_Object::isEmpty($element)) {
				unset($collection[$key]);
			}
			else {
				$this->compositeProperty($object, $element)->setValue($element, $object);
			}
		}

		return $collection;
	}

	//----------------------------------------------------------------------------- compositeProperty
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $object object
	 * @param $element object
	 * @return Reflection_Property
	 */
	protected function compositeProperty(object $object, object $element) : Reflection_Property
	{
		if (!$this->composite_property) {
			/** @noinspection PhpUnhandledExceptionInspection object */
			$link_class               = new Link_Class($element);
			$composite_class_name     = Builder::current()->sourceClassName(get_class($object));
			$this->composite_property = $link_class->getCompositeProperty($composite_class_name);
		}
		return $this->composite_property;
	}

}
