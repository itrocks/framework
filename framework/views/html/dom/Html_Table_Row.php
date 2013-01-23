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
	 * @param Html_Table_Cell $cell
	 */
	public function addCell(Html_Table_Cell $cell)
	{
		$this->cells[] = $cell;
	}

}
