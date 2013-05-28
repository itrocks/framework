<?php
namespace SAF\Framework;

/**
 * A DOM element class for HTML tables standard cells <td>
 */
class Html_Table_Standard_Cell extends Html_Table_Cell
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $content string
	 */
	public function __construct($content = null)
	{
		parent::__construct("td", $content);
	}

}
