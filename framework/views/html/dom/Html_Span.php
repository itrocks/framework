<?php
namespace SAF\Framework;

class Html_Span extends Dom_Element
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param string $content
	 */
	public function __construct($content = null)
	{
		parent::__construct("span");
		if (isset($content)) {
			$this->setContent($content);
		}
	}

}
