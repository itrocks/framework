<?php
namespace ITRocks\Framework\View\Html\Dom\List_;

use ITRocks\Framework\View\Html\Dom\Element;

/**
 * A DOM element class for HTML list item (<li>)
 */
class Item extends Element
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $content string
	 */
	public function __construct($content = null)
	{
		parent::__construct('li');
		if (isset($content)) {
			$this->setContent($content);
		}
	}

}
