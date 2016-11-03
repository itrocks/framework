<?php
namespace ITRocks\Framework\View\Html\Dom;

/**
 * <script> html dom element
 */
class Script extends Element
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Constructor
	 *
	 * @param $script string script
	 */
	public function __construct($script = '')
	{
		parent::__construct('script');
		$this->setContent($script);
	}

}
