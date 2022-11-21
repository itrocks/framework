<?php
namespace ITRocks\Framework\Layout\Structure\Draw;

use ITRocks\Framework\Layout\Structure\Element;
use ITRocks\Framework\Layout\Structure\Element\Has_Init;

/**
 * Horizontal or vertical snap line
 */
class Snap_Line extends Element implements Has_Init
{

	//----------------------------------------------------------------------------------- DUMP_SYMBOL
	const DUMP_SYMBOL = '¦';

	//---------------------------------------------------------------------------- $direction @values
	const HORIZONTAL = 'horizontal';
	const VERTICAL   = 'vertical';

	//------------------------------------------------------------------------------------ $direction
	/**
	 * @values self::const local
	 * @var string
	 */
	public string $direction;

	//------------------------------------------------------------------------------------------ init
	/**
	 * Initialize horizontal or vertical snap line
	 */
	public function init() : void
	{
		if (!isset($this->left)) {
			$this->direction = self::HORIZONTAL;
			$this->height    = 0;
			$this->left      = 0;
			$this->width     = $this->page->width;
		}
		if (!isset($this->top)) {
			$this->direction = self::VERTICAL;
			$this->height    = $this->page->height;
			$this->top       = 0;
			$this->width     = 0;
		}
	}

	//---------------------------------------------------------------------------------- isHorizontal
	/**
	 * @return boolean
	 */
	public function isHorizontal() : bool
	{
		return $this->direction === self::HORIZONTAL;
	}

	//------------------------------------------------------------------------------------ isVertical
	/**
	 * @return boolean
	 */
	public function isVertical() : bool
	{
		return $this->direction === self::VERTICAL;
	}

}