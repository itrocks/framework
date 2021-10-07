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
	 * @param $text  string      the text
	 * @param $font  string|null the font name
	 * @param $style string|null the font style
	 * @param $size  float|null  the font size
	 * @return float
	 */
	public function textWidth(
		string $text, string $font = null, string $style = null, float $size = null
	) : float;

}
