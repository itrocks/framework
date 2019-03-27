<?php
namespace ITRocks\Framework\View\Html\Dom\List_;

/**
 * Sorted list
 */
class Sorted extends Unordered
{

	//------------------------------------------------------------------------------------ getContent
	/**
	 * @return string
	 */
	public function getContent()
	{
		asort($this->items);
		return parent::getContent();
	}

}
