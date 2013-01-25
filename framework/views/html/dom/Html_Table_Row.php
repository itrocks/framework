<?php
namespace SAF\Framework;

class Html_Table_Row extends Dom_Element
{

	//---------------------------------------------------------------------------------------- $cells
	/**
	 * @contained
	 * @var Html_Table_Cell[]
	 */
	private $cells = array();

	//----------------------------------------------------------------------------------- __construct
	public function __construct()
	{
		parent::__construct("tr");
	}

	//------------------------------------------------------------------------------------ __toString
	public function __toString()
	{
		$this->setContent(join("", $this->cells));
		return parent::__toString();
	}

	//--------------------------------------------------------------------------------------- addCell
	/**
	 * @param $cell Html_Table_Cell
	 */
	public function addCell(Html_Table_Cell $cell)
	{
		$this->cells[] = $cell;
	}

}
