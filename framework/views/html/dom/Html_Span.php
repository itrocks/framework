<?php
namespace SAF\Framework;

/**
 * A DOM element class for HTML spans <span>
 */
class Html_Span extends Dom_Element
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $content string
	 */
	public function __construct($content = null)
	{
		parent::__construct('span');
		if (isset($content)) {
			$this->setContent($content);
		}
	}

}
