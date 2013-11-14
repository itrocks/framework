<?php
namespace SAF\Framework;

/**
 * A DOM element class for HTML tables <table>
 */
class Html_Table extends Dom_Element
{

	//----------------------------------------------------------------------------------------- $body
	/**
	 * @var Html_Table_Body
	 */
	public $body;

	//----------------------------------------------------------------------------------------- $head
	/**
	 * @var Html_Table_Head
	 */
	public $head;

	//----------------------------------------------------------------------------------- __construct
	/**
	 */
	public function __construct()
	{
		parent::__construct("table");
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		$content = "";
		if (isset($this->head)) $content .= "\n" . $this->head;
		if (isset($this->body)) $content .= "\n" . $this->body;
		$this->setContent($content . "\n");
		return "\n" . parent::__toString() . "\n";
	}

}
