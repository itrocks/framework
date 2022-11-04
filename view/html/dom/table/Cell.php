<?php
namespace ITRocks\Framework\View\Html\Dom\Table;

use ITRocks\Framework\View\Html\Dom\Element;

/**
 * A DOM element class for HTML tables cells (<td> and <th>, base class for standard and header cell)
 */
abstract class Cell extends Element
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $name    string|null
	 * @param $content string|null
	 */
	public function __construct(string $name = null, string $content = null)
	{
		parent::__construct($name);
		if (isset($content)) {
			$this->setContent($content);
		}
	}

}
