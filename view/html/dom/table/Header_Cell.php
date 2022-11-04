<?php
namespace ITRocks\Framework\View\Html\Dom\Table;

/**
 * A DOM element class for HTML tables header cells <th>
 */
class Header_Cell extends Cell
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $content string|null
	 */
	public function __construct(string $content = null)
	{
		parent::__construct('th', $content);
	}

}
