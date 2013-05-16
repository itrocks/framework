<?php
namespace SAF\Framework;

class Html_Button extends Dom_Element
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value string
	 * @param $id string
	 */
	public function __construct($value = null, $id = null)
	{
		parent::__construct("button", false);
		if (isset($value)) $this->setContent($value);
		if (isset($id))    $this->setAttribute("id", $id);
	}

}
