<?php
namespace ITRocks\Framework\Layout;

/**
 * Defines an output class, needed for pre-calculation during generation
 */
interface Output
{

	//------------------------------------------------------------------------------------ htmlHeight
	/**
	 * @param $text  string     the HTML text
	 * @param $width float|null the allowed width for the HTML text zone (null if unlimited)
	 * @param $size  float|null the font size
	 * @return float
	 */
	public function htmlHeight(string $text, float $width = null, float $size = null) : float;

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
