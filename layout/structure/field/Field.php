<?php
namespace ITRocks\Framework\Layout\Structure;

/**
 * A field will contain data, from constant (text) or property.path (property)
 */
abstract class Field extends Element
{

	//-------------------------------------------------------------------------- text align constants
	const CENTER = 'center';
	const LEFT   = 'left';
	const RIGHT  = 'right';

	//------------------------------------------------------------------------------------ $font_size
	/**
	 * @var integer
	 */
	public $font_size;

	//----------------------------------------------------------------------------------- $text_align
	/**
	 * @values self::const local
	 * @var string
	 */
	public $text_align;

	//------------------------------------------------------------------------------------------ hotX
	/**
	 * The "hot point" is the reference coordinate, depending on $text_align
	 *
	 * @return float
	 */
	public function hotX()
	{
		switch ($this->text_align) {
			case static::CENTER: return $this->left + ($this->width / 2);
			case static::RIGHT:  return $this->left + $this->width;
			default:             return $this->left;
		}
	}

}
