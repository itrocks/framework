<?php
namespace ITRocks\Framework\Layout\Structure;

/**
 * A field will contain data, from constant (text) or property.path (property)
 */
class Field extends Element
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

}
