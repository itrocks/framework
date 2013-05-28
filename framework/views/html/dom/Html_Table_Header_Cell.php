<?php
namespace SAF\Framework;

/**
 * A DOM element class for HTML tables header cells <th>
 */
class Html_Table_Header_Cell extends Html_Table_Cell
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $content string
	 */
	public function __construct($content = null)
	{
		parent::__construct("th", $content);
	}

}
