<?php
namespace ITRocks\Framework\Widget;

use ITRocks\Framework\Feature\Edit\Html_Builder_Property;
use ITRocks\Framework\View\Html\Builder\Property;
use ITRocks\Framework\View\Html\Dom\Div;

/**
 * A widget to open a property in edit mode, even if in an output view
 */
class Edit extends Property
{

	//------------------------------------------------------------------------------------- buildHtml
	/**
	 * @return string
	 */
	public function buildHtml()
	{
		$builder = new Html_Builder_Property($this->property, $this->value);
		return new Div($builder->build());
	}

}
