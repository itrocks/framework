<?php
namespace SAF\Framework;

class Html_Textarea extends Dom_Element
{

	//----------------------------------------------------------------------------------- __construct
	public function __construct($name = null, $value = null)
	{
		parent::__construct("textarea");
		if (isset($name))  $this->setAttribute("name", $name);
		if (isset($value)) $this->setContent($value);
	}

}
