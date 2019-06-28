<?php
namespace ITRocks\Framework\Widget;

use ITRocks\Framework\Feature\Edit\Html_Builder_Property;
use ITRocks\Framework\View\Html\Builder\Property;

/**
 * The standard widget for a Color
 */
class Color extends Property
{

	//------------------------------------------------------------------------------------- buildHtml
	/**
	 * @return string
	 */
	public function buildHtml()
	{
		$builder = new Html_Builder_Property($this->property, $this->value);
		$builder->setTemplate($this->template);
		$builder->attributes['type'] = 'color';
		return $builder->build();
	}

}
