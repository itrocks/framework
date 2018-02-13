<?php
namespace ITRocks\Framework\Widget\Edit\Widgets;

use ITRocks\Framework\Mapper\Empty_Object;
use ITRocks\Framework\Mapper\Object_Builder_Array;
use ITRocks\Framework\View\Html\Builder\Collection;
use ITRocks\Framework\View\Html\Builder\Property;
use ITRocks\Framework\Widget\Edit\Html_Builder_Collection;
use ITRocks\Framework\Widget\Edit\Html_Template;

/**
 * This property widget displays a Map like a collection
 */
class Map_As_Collection extends Property
{

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
			// - edit
			if ($this->template instanceof Html_Template) {
				$collection = new Html_Builder_Collection($this->property, $this->value);
				$collection->setTemplate($this->template);
			}
			// - output
			else {
				$collection = new Collection($this->property, $this->value);
			}
			// build
			return $collection->build();
		}
		else {
			return $this->value;
		}
	}

	//------------------------------------------------------------------------------------ buildValue
	/**
	 * @param $object        object
	 * @param $null_if_empty boolean
	 * @return mixed
	 */
	public function buildValue($object, $null_if_empty)
	{
		$builder = new Object_Builder_Array();
		$objects = $builder->buildCollection($this->property->getType()->getElementTypeAsString(), $this->value);

		// Remove empty objects from collection to avoid control on null value
		if ($objects) {
			foreach ($objects as $key => $object) {
				if (Empty_Object::isEmpty($object)) {
					unset($objects[$key]);
				}
			}
		}

		return $objects;
	}

}
