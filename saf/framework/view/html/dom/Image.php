<?php
namespace SAF\Framework\View\Html\Dom;

/**
 * Html image DOM element
 */
class Image extends Element
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $source string
	 */
	public function __construct($source = null)
	{
		parent::__construct('img');
		if (isset($source)) $this->setAttribute('src', $source);
	}

}
