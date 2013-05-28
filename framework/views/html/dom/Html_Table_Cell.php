<?php
namespace SAF\Framework;

/**
 * A DOM element class for HTML tables cells (<td> and <th>, base class for standard and header cell)
 */
abstract class Html_Table_Cell extends Dom_Element
{

	//------------------------------------------------------------------------------------ __contruct
	/**
	 * @param $name    string
	 * @param $content string
	 */
	public function __construct($name = null, $content = null)
	{
		parent::__construct($name);
		if (isset($content)) {
			$this->setContent($content);
		}
	}

}
