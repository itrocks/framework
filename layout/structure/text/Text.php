<?php
namespace ITRocks\Framework\Layout\Structure;

/**
 * Text field : here to display text
 */
class Text extends Element
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

	//----------------------------------------------------------------------------------------- $text
	/**
	 * @var string
	 */
	public $text;

	//----------------------------------------------------------------------------------- $text_align
	/**
	 * @values self::const local
	 * @var string
	 */
	public $text_align;

}
