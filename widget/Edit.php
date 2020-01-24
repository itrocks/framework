<?php
namespace ITRocks\Framework\Widget;

use ITRocks\Framework\Feature\Edit\Html_Builder_Property;
use ITRocks\Framework\View\Html\Builder\Property;

/**
 * A widget to open a property in edit mode, even if in an output view
 */
class Edit extends Property
{

	//------------------------------------------------------------------------------------- buildHtml
	/**
	 * @inheritDoc
	 */
	public function buildHtml()
	{
		$builder = new Html_Builder_Property($this->property, $this->value);
		return $builder->build();
	}

}
