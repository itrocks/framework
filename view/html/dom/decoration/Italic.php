<?php
namespace ITRocks\Framework\View\Html\Dom\Decoration;

use ITRocks\Framework\View\Html\Dom\Element;

/**
 * A DOM element class for HTML italic decoration item (<i>)
 */
class Italic extends Element
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $content string|null
	 */
	public function __construct(string $content = null)
	{
		parent::__construct('i');
		if (isset($content)) {
			$this->setContent($content);
		}
	}

}
