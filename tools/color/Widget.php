<?php
namespace ITRocks\Framework\Tools\Color;

use ITRocks\Framework\View\Html;
use ITRocks\Framework\Widget\Edit\Html_Builder_Property;

/**
 * The standard widget for a Color
 */
class Widget extends Html\Builder\Property
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
