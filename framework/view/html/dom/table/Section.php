<?php
namespace SAF\Framework\View\Html\Dom\Table;

use SAF\Framework\View\Html\Dom\Element;

/**
 * A DOM element class for HTML tables sections (multiple rows)
 */
abstract class Section extends Element
{

	//----------------------------------------------------------------------------------------- $rows
	/**
	 * @var Row[]
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
	 * @param $row Row
	 */
	public function addRow(Row $row)
	{
		$this->rows[] = $row;
	}

}
