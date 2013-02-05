<?php
namespace SAF\Framework;

class Html_Table extends Dom_Element
{

	//----------------------------------------------------------------------------------------- $body
	/**
	 * @component
	 * @var Html_Table_Body
	 */
	private $body;

	//----------------------------------------------------------------------------------------- $head
	/**
	 * @component
	 * @var Html_Table_Head
	 */
	private $head;

	//----------------------------------------------------------------------------------- __construct
	public function __construct()
	{
		parent::__construct("table");
	}

	//------------------------------------------------------------------------------------ __toString
	public function __toString()
	{
		$content = "";
		if (isset($this->head)) $content .= "\n" . $this->head;
		if (isset($this->body)) $content .= "\n" . $this->body;
		$this->setContent($content . "\n");
		return "\n" . parent::__toString() . "\n";
	}

	//--------------------------------------------------------------------------------------- setBody
	public function setBody(Html_Table_Body $body)
	{
		$this->body = $body;
	}

	//--------------------------------------------------------------------------------------- setHead
	public function setHead(Html_Table_Head $head)
	{
		$this->head = $head;
	}

}
