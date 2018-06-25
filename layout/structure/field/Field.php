<?php
namespace ITRocks\Framework\Layout\Structure;

use ITRocks\Framework\Layout\Structure\Element\Has_Init;

/**
 * A field will contain data, from constant (text) or property.path (property)
 */
abstract class Field extends Element implements Has_Init
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

	//------------------------------------------------------------------------------------------ init
	public function init()
	{
		if ($this->font_size && !$this->height) {
			$this->height = $this->font_size;
		}
	}

}
