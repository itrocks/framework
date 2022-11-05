<?php
namespace ITRocks\Framework\Widget;

use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Target;
use ITRocks\Framework\View;
use ITRocks\Framework\View\Html\Builder\Property;
use ITRocks\Framework\View\Html\Dom\Anchor;
use ITRocks\Framework\View\Html\Dom\Div;
use ITRocks\Framework\View\Html\Template;

/**
 * Website widget : display a link as link with target="_blank"
 */
class Website extends Property
{

	//------------------------------------------------------------------------------------- buildHtml
	/**
	 * @return string
	 */
	public function buildHtml() : string
	{
		if ($this->template->getFeature() !== Feature::F_OUTPUT) {
			return Template::ORIGIN;
		}
		$link = $this->value;
		if (!str_contains($link, '://')) {
			$link = 'https://' . $link;
		}
		$value  = rParse($this->value, '://', 1, true);
		$anchor = new Anchor($link, $value);
		$anchor->setAttribute(View::TARGET, Target::NEW_WINDOW);
		return new Div($anchor);
	}

}
