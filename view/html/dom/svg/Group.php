<?php
namespace ITRocks\Framework\View\Html\Dom\Svg;

use ITRocks\Framework\View\Html\Dom\Element;

/**
 * A DOM SVG group (g) element
 */
class Group extends Element
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Group constructor
	 */
	public function __construct()
	{
		parent::__construct('g');
	}

}
