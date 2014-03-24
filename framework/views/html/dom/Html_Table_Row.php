<?php
namespace SAF\Framework;

/**
 * A DOM element class for HTML tables rows <tr>
 */
class Html_Table_Row extends Dom_Element
{

	//---------------------------------------------------------------------------------------- $cells
	/**
	 * @var Html_Table_Cell[]
	 */
	private $cells = [];

	//----------------------------------------------------------------------------------- __construct
	/**
	 */
	public function __construct()
	{
		parent::__construct('tr');
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
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
	 * @param $cell            Html_Table_Cell
	 * @param $before_position integer first cell position is 0
	 */
	public function addCell(Html_Table_Cell $cell, $before_position = null)
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
