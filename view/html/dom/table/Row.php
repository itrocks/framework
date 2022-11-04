<?php
namespace ITRocks\Framework\View\Html\Dom\Table;

use ITRocks\Framework\View\Html\Dom\Element;

/**
 * A DOM element class for HTML tables rows <tr>
 */
class Row extends Element
{

	//---------------------------------------------------------------------------------------- $cells
	/**
	 * @var Cell[]
	 */
	private array $cells = [];

	//----------------------------------------------------------------------------------- __construct
	public function __construct()
	{
		parent::__construct('tr');
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		if (count($this->cells) > 1) {
			$this->setContent(LF . join(LF, $this->cells) . LF);
		}
		else {
			$this->setContent(join('', $this->cells));
		}
		return parent::__toString();
	}

	//--------------------------------------------------------------------------------------- addCell
	/**
	 * @param $cell            Cell
	 * @param $before_position integer|null first cell position is 0
	 */
	public function addCell(Cell $cell, int $before_position = null) : void
	{
		if (isset($before_position)) {
			$this->cells = array_merge(
				array_slice($this->cells, 0, $before_position),
				[$cell],
				array_slice($this->cells, $before_position, count($this->cells) - $before_position)
			);
		}
		else {
			$this->cells[] = $cell;
		}
	}

}
