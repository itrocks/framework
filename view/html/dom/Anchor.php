<?php
namespace ITRocks\Framework\View\Html\Dom;

/**
 * A DOM element class for HTML anchors <a>
 */
class Anchor extends Element
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $link    string|null
	 * @param $content string|null
	 */
	public function __construct(string $link = null, string $content = null)
	{
		parent::__construct('a');
		if (isset($link))    $this->setAttribute('href', $link);
		if (isset($content)) $this->setContent($content);
	}

}
