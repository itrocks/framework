<?php
namespace SAF\Framework\View\Html\Dom\Table;

/**
 * A DOM element class for HTML tables standard cells <td>
 */
class Standard_Cell extends Cell
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $content string
	 */
	public function __construct($content = null)
	{
		parent::__construct('td', $content);
	}

}
