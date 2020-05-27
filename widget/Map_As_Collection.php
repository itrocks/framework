<?php
namespace ITRocks\Framework\Widget;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Feature\Edit\Html_Builder_Collection;
use ITRocks\Framework\Feature\Edit\Html_Template;
use ITRocks\Framework\Mapper\Empty_Object;
use ITRocks\Framework\Mapper\Object_Builder_Array;
use ITRocks\Framework\Reflection\Annotation\Property\Widget_Annotation;
use ITRocks\Framework\Traits\Is_Immutable;
use ITRocks\Framework\View\Html\Builder\Collection;
use ITRocks\Framework\View\Html\Builder\Property;

/**
 * This property widget displays a Map like a collection
 */
class Map_As_Collection extends Property
{

	//------------------------------------------------------------------------------------- $pre_path
	/**
	 * @var string
	 */
	public $pre_path = null;

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
			// - immutable objects : disconnect values
			foreach ($this->value as $value) {
				if (isA($value, Is_Immutable::class)) {
					Dao::disconnect($value);
				}
			}
			// - edit
			if ($this->template instanceof Html_Template) {
				$collection = new Html_Builder_Collection(
					$this->property, $this->value, true, $this->pre_path
				);
				$collection->setTemplate($this->template);
			}
			// - output
			else {
				$collection = new Collection($this->property, $this->value, true);
			}
			$collection->sort = Widget_Annotation::of($this->property)->option('sort', true);
			// build
			return $collection->build();
		}
		if (isA($this->value, Is_Immutable::class)) {
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
		$builder    = new Object_Builder_Array();
		$class_name = $this->property->getType()->getElementTypeAsString();
		/** @noinspection PhpUnhandledExceptionInspection */
		$old_value  = $this->property->getValue($object);
		$objects    = $builder->buildCollection($class_name, $old_value, $this->value);

		// Remove empty objects from collection to avoid control on null value
		foreach ($objects as $key => $object) {
			if (Empty_Object::isEmpty($object)) {
				unset($objects[$key]);
			}
		}

		return $objects;
	}

}
