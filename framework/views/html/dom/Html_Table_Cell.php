<?php
namespace SAF\Framework;

abstract class Html_Table_Cell extends Dom_Element
{

	//------------------------------------------------------------------------------------ __contruct
	public function __construct($name = null, $content = null)
	{
		parent::__construct($name);
		if (isset($content)) {
			$this->setContent($content);
		}
	}

}
