<?php
namespace ITRocks\Framework\Layout\Structure;

use ITRocks\Framework\Reflection\Attribute\Property\Values;

/**
 * A field will contain data, from constant (text) or property.path (property)
 */
abstract class Field extends Element
{

	//-------------------------------------------------------------------------- text align constants
	const CENTER = 'center';
	const LEFT   = 'left';
	const RIGHT  = 'right';

	//---------------------------------------------------------------------------------------- $color
	/** @max_length 6 */
	public string $color = '000000';

	//------------------------------------------------------------------------------------ $font_size
	public float $font_size;

	//---------------------------------------------------------------------------------- $font_weight
	public string $font_weight = '';

	//----------------------------------------------------------------------------------- $text_align
	#[Values(self::class, Values::LOCAL)]
	public string $text_align;

	//------------------------------------------------------------------------------------------ hotX
	/** The 'hot point' is the reference coordinate, depending on $text_align */
	public function hotX() : float
	{
		switch ($this->text_align) {
			case static::CENTER: return $this->left + ($this->width / 2);
			case static::RIGHT:  return $this->left + $this->width;
			default:             return $this->left;
		}
	}

}
