<?php
namespace SAF\Framework;

class Html_Table_Standard_Cell extends Html_Table_Cell
{

	//----------------------------------------------------------------------------------- __construct
	public function __construct($content = null)
	{
		parent::__construct("td", $content);
	}

}
