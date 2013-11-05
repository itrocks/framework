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
	private $cells = array();

	//----------------------------------------------------------------------------------- __construct
	/**
	 */
	public function __construct()
	{
		parent::__construct("tr");
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		if (count($this->cells) > 1) {
			$this->setContent("\n" . join("\n", $this->cells) . "\n");
		}
		else {
			$this->setContent(join("", $this->cells));
		}
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
