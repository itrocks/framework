<?php
namespace SAF\Framework;

/**
 * Html image DOM element
 */
class Html_Image extends Dom_Element
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $source string
	 */
	public function __construct($source = null)
	{
		parent::__construct("img");
		if (isset($source)) $this->setAttribute("src", $source);
	}

}
