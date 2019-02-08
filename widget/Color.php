<?php
namespace ITRocks\Framework\Widget;

use ITRocks\Framework\Feature\Edit\Html_Builder_Property;
use ITRocks\Framework\View\Html;

/**
 * The standard widget for a Color
 */
class Color extends Html\Builder\Property
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
