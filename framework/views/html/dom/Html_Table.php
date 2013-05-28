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
	private $body;

	//----------------------------------------------------------------------------------------- $head
	/**
	 * @var Html_Table_Head
	 */
	private $head;

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

	//--------------------------------------------------------------------------------------- setBody
	/**
	 * @param $body Html_Table_Body
	 */
	public function setBody(Html_Table_Body $body)
	{
		$this->body = $body;
	}

	//--------------------------------------------------------------------------------------- setHead
	/**
	 * @param $head Html_Table_Head
	 */
	public function setHead(Html_Table_Head $head)
	{
		$this->head = $head;
	}

}
