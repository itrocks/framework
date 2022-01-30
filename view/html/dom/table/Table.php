<?php
namespace ITRocks\Framework\View\Html\Dom;

use ITRocks\Framework\View\Html\Dom\Table\Body;
use ITRocks\Framework\View\Html\Dom\Table\Footer;
use ITRocks\Framework\View\Html\Dom\Table\Head;

/**
 * A DOM element class for HTML tables <table>
 */
class Table extends Element
{

	//----------------------------------------------------------------------------------------- $body
	/**
	 * @var Body
	 */
	public $body;

	//--------------------------------------------------------------------------------------- $footer
	/**
	 * @var Footer
	 */
	public $footer;
	
	//----------------------------------------------------------------------------------------- $head
	/**
	 * @var Head
	 */
	public $head;

	//----------------------------------------------------------------------------------- __construct
	/**
	 */
	public function __construct()
	{
		parent::__construct('table');
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		$content = '';
		if (isset($this->head)) $content   .= LF . $this->head;
		if (isset($this->body)) $content   .= LF . $this->body;
		if (isset($this->footer)) $content .= LF . $this->footer;
		$this->setContent($content . LF);
		return parent::__toString();
	}

}
