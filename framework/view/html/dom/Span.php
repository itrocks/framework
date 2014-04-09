<?php
namespace SAF\Framework\View\Html\Dom;

/**
 * A DOM element class for HTML spans <span>
 */
class Span extends Element
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
