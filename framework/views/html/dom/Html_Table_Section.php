<?php
namespace SAF\Framework;

/**
 * A DOM element class for HTML tables sections (multiple rows)
 */
abstract class Html_Table_Section extends Dom_Element
{

	//----------------------------------------------------------------------------------------- $rows
	/**
	 * @var Html_Table_Row[]
	 */
	public $rows = [];

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		$this->setContent(LF . join(LF, $this->rows) . LF);
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
