<?php
namespace ITRocks\Framework\Layout\Structure\Draw;

use ITRocks\Framework\Layout\Structure\Element;
use ITRocks\Framework\Layout\Structure\Element\Has_Init;

/**
 * Horizontal or vertical snap line
 */
class Snap_Line extends Element implements Has_Init
{

	//---------------------------------------------------------------------------- $direction @values
	const HORIZONTAL = 'horizontal';
	const VERTICAL   = 'vertical';

	//------------------------------------------------------------------------------------ $direction
	/**
	 * @values self::const local
	 * @var string
	 */
	public $direction = self::VERTICAL;

	//------------------------------------------------------------------------------------------ init
	/**
	 * Initialize horizontal or vertical snap line
	 */
	public function init()
	{
		if (!$this->left) {
			$this->height = $this->page->height;
			$this->left   = 0;
			$this->width  = 0;
		}
		if (!$this->top) {
			$this->height = 0;
			$this->top    = 0;
			$this->width  = $this->page->width;
		}
	}

	//---------------------------------------------------------------------------------- isHorizontal
	/**
	 * @return boolean
	 */
	public function isHorizontal()
	{
		return $this->direction === self::HORIZONTAL;
	}

	//------------------------------------------------------------------------------------ isVertical
	/**
	 * @return boolean
	 */
	public function isVertical()
	{
		return $this->direction === self::VERTICAL;
	}

}
