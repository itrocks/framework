<?php
namespace SAF\Framework;

/**
 * A DOM element class for HTML form inputs <input>
 */
class Html_Input extends Dom_Element
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $name  string
	 * @param $value string
	 * @param $id    string
	 */
	public function __construct($name = null, $value = null, $id = null)
	{
		parent::__construct("input", false);
		if (isset($name))  $this->setAttribute("name",  $name);
		if (isset($value)) $this->setAttribute("value", $value);
		if (isset($id))    $this->setAttribute("id",    $id);
	}

}
