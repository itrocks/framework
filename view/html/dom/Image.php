<?php
namespace ITRocks\Framework\View\Html\Dom;

/**
 * Html image DOM element
 */
class Image extends Element
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $source string|null
	 */
	public function __construct(string $source = null)
	{
		parent::__construct('img');
		if (isset($source)) $this->setAttribute('src', $source);
	}

}
