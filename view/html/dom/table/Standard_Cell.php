<?php
namespace ITRocks\Framework\View\Html\Dom\Table;

/**
 * A DOM element class for HTML tables standard cells <td>
 */
class Standard_Cell extends Cell
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $content string|null
	 */
	public function __construct(string $content = null)
	{
		parent::__construct('td', $content);
	}

}
