<?php
namespace SAF\Framework\View\Html\Dom\Table;

/**
 * A DOM element class for HTML tables header cells <th>
 */
class Header_Cell extends Cell
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $content string
	 */
	public function __construct($content = null)
	{
		parent::__construct('th', $content);
	}

}
