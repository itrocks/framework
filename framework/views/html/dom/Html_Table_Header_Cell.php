<?php
namespace SAF\Framework;

class Html_Table_Header_Cell extends Html_Table_Cell
{

	//----------------------------------------------------------------------------------- __construct
	public function __construct($content = null)
	{
		parent::__construct("th", $content);
	}

}
