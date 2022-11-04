<?php
namespace ITRocks\Framework\View\Html\Dom\Table;

use ITRocks\Framework\View\Html\Dom\Element;

/**
 * A DOM element class for HTML tables sections (multiple rows)
 */
abstract class Section extends Element
{

	//----------------------------------------------------------------------------------------- $rows
	/**
	 * @var Row[]
	 */
	public array $rows = [];

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		$this->setContent(LF . join(LF, $this->rows) . LF);
		return parent::__toString();
	}

	//---------------------------------------------------------------------------------------- addRow
	/**
	 * @param $row Row
	 */
	public function addRow(Row $row) : void
	{
		$this->rows[] = $row;
	}

}
