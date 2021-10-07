<?php
namespace ITRocks\Framework\Layout\Structure\Field;

use ITRocks\Framework\Layout\Structure\Element\Has_Init;
use ITRocks\Framework\Layout\Structure\Field;
use ITRocks\Framework\Layout\Structure\Field\Text\Formatted;
use ITRocks\Framework\Layout\Structure\Field\Text\Templating;

/**
 * Text field : here to display text
 */
class Text extends Field implements Has_Init
{
	use Formatted;
	use Templating;

	//----------------------------------------------------------------------------------------- $text
	/**
	 * @var string
	 */
	public $text;

	//------------------------------------------------------------------------------- calculateHeight
	/**
	 * Calculate the height of the zone depending on the number of lines of text and the font height
	 */
	public function calculateHeight()
	{
		$this->height = strlen($this->text)
			? ($this->font_size * (substr_count($this->text, LF) + 1))
			: 0;
	}

	//------------------------------------------------------------------------------------------ dump
	/**
	 * @param $level integer
	 * @return string
	 */
	public function dump($level = 0)
	{
		return parent::dump($level) . ' = ' . $this->text;
	}

	//------------------------------------------------------------------------------------------ init
	public function init()
	{
		if ($this->font_size && !$this->height) {
			$this->calculateHeight();
		}
	}

}
