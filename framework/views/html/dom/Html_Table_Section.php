<?php
namespace SAF\Framework;

abstract class Html_Table_Section extends Dom_Element
{

	//----------------------------------------------------------------------------------------- $rows
	/**
	 * @component
	 * @var Html_Table_Row[]
	 */
	public $rows = array();

	//------------------------------------------------------------------------------------ __toString
	public function __toString()
	{
		$this->setContent("\n" . join("\n", $this->rows) . "\n");
		return parent::__toString();
	}

	//---------------------------------------------------------------------------------------- addRow
	/**
	 * @param $row Html_Table_Row
	 */
	public function addRow(Html_Table_Row $row)
	{
		$this->rows[] = $row;
	}

}
