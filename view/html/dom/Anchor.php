<?php
namespace ITRocks\Framework\View\Html\Dom;

/**
 * A DOM element class for HTML anchors <a>
 */
class Anchor extends Element
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $link    string
	 * @param $content string
	 */
	public function __construct($link = null, $content = null)
	{
		parent::__construct('a');
		if (isset($link))    $this->setAttribute('href', $link);
		if (isset($content)) $this->setContent($content);
	}

}
