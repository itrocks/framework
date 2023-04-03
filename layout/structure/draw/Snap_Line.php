<?php
namespace ITRocks\Framework\Layout\Structure\Draw;

use ITRocks\Framework\Layout\Structure\Element;
use ITRocks\Framework\Layout\Structure\Element\Has_Init;
use ITRocks\Framework\Reflection\Attribute\Property\Values;

/**
 * Horizontal or vertical snap line
 */
class Snap_Line extends Element implements Has_Init
{

	//----------------------------------------------------------------------------------- DUMP_SYMBOL
	const DUMP_SYMBOL = '¦';

	//---------------------------------------------------------------------------- $direction #Values
	const HORIZONTAL = 'horizontal';
	const VERTICAL   = 'vertical';

	//------------------------------------------------------------------------------------ $direction
	#[Values(self::class, Values::LOCAL)]
	public string $direction;

	//------------------------------------------------------------------------------------------ init
	/** Initialize horizontal or vertical snap line */
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
	public function isHorizontal() : bool
	{
		return $this->direction === self::HORIZONTAL;
	}

	//------------------------------------------------------------------------------------ isVertical
	public function isVertical() : bool
	{
		return $this->direction === self::VERTICAL;
	}

}
