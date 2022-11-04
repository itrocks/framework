<?php
namespace ITRocks\Framework\View\Html\Dom;

/**
 * A DOM element class for HTML div (<div>)
 */
class Div extends Element
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $content string|null
	 */
	public function __construct(string $content = null)
	{
		parent::__construct('div');
		if (isset($content)) {
			$this->setContent($content);
		}
	}

}
