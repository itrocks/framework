<?php
namespace ITRocks\Framework\Layout;

/**
 * Defines an output class, needed for pre-calculation during generation
 */
interface Output
{

	//------------------------------------------------------------------------------------- textWidth
	/**
	 * Get text width calculated by the output generator
	 *
	 * @param $text string  the text
	 * @param $font string  the font name
	 * @param $style string the font style
	 * @param $size float   the font size
	 * @return float
	 */
	public function textWidth($text, $font, $style = null, $size = null);

}
