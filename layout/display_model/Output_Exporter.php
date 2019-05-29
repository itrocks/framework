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
	public $structure;

	//------------------------------------------------------------------------------------ exportHtml
	/**
	 * Export the structure into an output HTML template for the class
	 */
	public function exportHtml()
	{
		echo "wanna export some HTML ?";
	}

	//------------------------------------------------------------------------------------- textWidth
	/**
	 * Get text width calculated by the output generator
	 *
	 * Here we do not deal with automatic carriage return : always consider text width as 0
	 *
	 * @param $text string  the text
	 * @param $font string  the font name
	 * @param $style string the font style
	 * @param $size float   the font size
	 * @return float
	 */
	public function textWidth($text, $font, $style = null, $size = null)
	{
		return 0;
	}

}
