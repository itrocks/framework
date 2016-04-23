<?php
namespace SAF\Framework\View\Html\Dom\Table;

use SAF\Framework\View\Html\Dom\Element;

/**
 * A DOM element class for HTML tables cells (<td> and <th>, base class for standard and header cell)
 */
abstract class Cell extends Element
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $name    string
	 * @param $content string
	 */
	public function __construct($name = null, $content = null)
	{
		parent::__construct($name);
		if (isset($content)) {
			$this->setContent($content);
		}
	}

}
