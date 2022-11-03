<?php
namespace ITRocks\Framework\Layout\Display_Model;

use ITRocks\Framework\Layout\Output;
use ITRocks\Framework\Layout\Structure;

/**
 * Display model output exporter
 */
class Output_Exporter implements Output
{

	//------------------------------------------------------------------------------------ $structure
	/**
	 * @var Structure
	 */
	public Structure $structure;

	//------------------------------------------------------------------------------------ exportHtml
	/**
	 * Export the structure into an output HTML template for the class
	 */
	public function exportHtml()
	{
		echo "wanna export some HTML ?";
	}

	//------------------------------------------------------------------------------------ htmlHeight
	/**
	 * @param $text  string     the HTML text
	 * @param $width float|null the allowed width for the HTML text zone (null if unlimited)
	 * @param $size  float|null the font size
	 * @return float
	 */
	public function htmlHeight(string $text, float $width = null, float $size = null) : float
	{
		return .0;
	}

	//------------------------------------------------------------------------------------- textWidth
	/**
	 * Get text width calculated by the output generator
	 *
	 * Here we do not deal with automatic carriage return : always consider text width as 0
	 *
	 * @param $text  string      the text
	 * @param $font  string|null the font name
	 * @param $style string|null the font style
	 * @param $size  float|null  the font size
	 * @return float
	 */
	public function textWidth(
		string $text, string $font = null, string $style = null, float $size = null
	) : float
	{
		return .0;
	}

}
