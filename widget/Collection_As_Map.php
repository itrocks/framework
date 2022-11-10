<?php
namespace ITRocks\Framework\Widget;

use ITRocks\Framework\Feature\Edit\Html_Builder_Map;
use ITRocks\Framework\Feature\Edit\Html_Builder_Property;
use ITRocks\Framework\Feature\Edit\Html_Template;
use ITRocks\Framework\Mapper\Object_Builder_Array;
use ITRocks\Framework\View\Html\Builder\Map;
use ITRocks\Framework\View\Html\Builder\Property;
use ITRocks\Framework\View\User_Error_Exception;

/**
 * This property widget displays a collection as if it was a map
 */
class Collection_As_Map extends Property
{

	//------------------------------------------------------------------------------------- buildHtml
	/**
	 * @return string
	 */
	public function buildHtml() : string
	{
		// TODO LOW remove this "if" statement, trigger a notice, debug step by step and optimize
		// this "if" patch is here because parseSingleValue() calls this both : we have to build html
		// on first pass only.
		if (is_array($this->value)) {
			// - edit
			if ($this->template instanceof Html_Template) {
				$map = new Html_Builder_Map($this->property, $this->value, $this->getFieldName());
				$map->setTemplate($this->template);
			}
			// - output
			else {
				$map = new Map($this->property, $this->value);
			}
			// build
			return $map->build();
		}
		return strval($this->value);
	}

	//------------------------------------------------------------------------------------ buildValue
	/**
	 * @param $object        object
	 * @param $null_if_empty boolean
	 * @return object[]
	 * @throws User_Error_Exception
	 */
	public function buildValue(object $object, bool $null_if_empty) : array
	{
		$builder = new Object_Builder_Array();
		return $builder->buildMap($this->value, $this->property->getType()->getElementTypeAsString());
	}

	//---------------------------------------------------------------------------------- getFieldName
	/**
	 * @return string
	 */
	protected function getFieldName() : string
	{
		$builder = new Html_Builder_Property($this->property);
		return $builder->getFieldName();
	}

}
