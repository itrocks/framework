<?php
namespace ITRocks\Framework\Layout\Structure;

use ITRocks\Framework\Layout\Structure\Draw\Snap_Line;

/**
 * A structured page
 */
class Page
{

	//----------------------------------------------------------- page position information constants
	/**
	 * It is independent but must be the same special values than Model\Page constants
	 */
	const ALL    = 'A';
	const FIRST  = '1';
	const LAST   = '-1';
	const MIDDLE = '0';

	//------------------------------------------------------------------------------------- $elements
	/**
	 * @var Element[]
	 */
	public $elements = [];

	//--------------------------------------------------------------------------------------- $height
	/**
	 * @var float
	 */
	public $height = 297;

	//--------------------------------------------------------------------------------------- $number
	/**
	 * Page number
	 *
	 * '-*' and 'A' are temporary page numbers from the source structure :
	 * they will be replaced by final pages during the generation process.
	 *
	 * @signed
	 * @var integer|string
	 */
	public $number;

	//---------------------------------------------------------------------------------------- $width
	/**
	 * @var float
	 */
	public $width = 210;

	//--------------------------------------------------------------------------------------- isEmpty
	/**
	 * @return boolean true if the page contains no visible elements
	 */
	public function isEmpty()
	{
		if (!$this->elements) {
			return true;
		}
		foreach ($this->elements as $element) {
			if (!(
				($element instanceof Snap_Line)
				|| ($element instanceof Group)
			)) {
				return false;
			}
		}
		return true;
	}

}
