<?php
namespace ITRocks\Framework\View\Html\Dom;

/**
 * A DOM element class for HTML labels <label>
 */
class Label extends Element
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $content string|null
	 */
	public function __construct(string $content = null)
	{
		parent::__construct('label');
		if (isset($content)) {
			$this->setContent($content);
		}
	}

}
