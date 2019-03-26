<?php
namespace ITRocks\Framework\View\Html\Dom\List_;

use ITRocks\Framework\View\Html\Dom\List_;

/**
 * A DOM ordered list element
 */
class Ordered extends List_
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $items string[]|null
	 */
	public function __construct(array $items = null)
	{
		parent::__construct('ol', $items);
	}

}
