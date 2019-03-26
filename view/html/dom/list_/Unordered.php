<?php
namespace ITRocks\Framework\View\Html\Dom\List_;

use ITRocks\Framework\View\Html\Dom\List_;

/**
 * A DOM unordered list element
 */
class Unordered extends List_
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $items string[]|null
	 */
	public function __construct(array $items = null)
	{
		parent::__construct('ul', $items);
	}

}
