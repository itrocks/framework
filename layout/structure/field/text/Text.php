<?php
namespace ITRocks\Framework\Layout\Structure\Field;

use ITRocks\Framework\Layout\Output;
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
	public string $text;

	//------------------------------------------------------------------------------- calculateHeight
	/**
	 * Calculate the height of the zone depending on the number of lines of text and the font height
	 *
	 * @output $height
	 * @param $output Output|null Allow formatted text output calculation
	 * @return float
	 */
	public function calculateHeight(Output $output = null) : float
	{
		if ($output && $this->isFormatted()) {
			$this->height = $output->htmlHeight($this->text, $this->width, $this->font_size);
		}
		else {
			$this->height = strlen($this->text)
				? ($this->font_size * (substr_count($this->text, LF) + 1))
				: 0;
		}
		return $this->height;
	}

	//------------------------------------------------------------------------------------------ dump
	/**
	 * @param $level integer
	 * @return string
	 */
	public function dump(int $level = 0) : string
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
