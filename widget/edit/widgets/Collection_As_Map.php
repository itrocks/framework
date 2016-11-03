<?php
namespace ITRocks\Framework\Widget\Edit\Widgets;

use ITRocks\Framework\Mapper\Object_Builder_Array;
use ITRocks\Framework\View\Html\Builder\Map;
use ITRocks\Framework\View\Html\Builder\Property;
use ITRocks\Framework\Widget\Edit\Html_Builder_Map;
use ITRocks\Framework\Widget\Edit\Html_Template;

/**
 * This property widget displays a collection as if it was a map
 */
class Collection_As_Map extends Property
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
				$map = new Html_Builder_Map($this->property, $this->value);
				$map->setTemplate($this->template);
			}
			// - output
			else {
				$map = new Map($this->property, $this->value);
			}
			// build
			return $map->build();
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
		return $builder->buildMap($this->value, $this->property->getType()->getElementTypeAsString());
	}

}
