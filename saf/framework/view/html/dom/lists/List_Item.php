<?php
namespace SAF\Framework\View\Html\Dom\Lists;

use SAF\Framework\View\Html\Dom\Element;

/**
 * A DOM element class for HTML tables cells (<td> and <th>, base class for standard and header cell)
 */
class List_Item extends Element
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
