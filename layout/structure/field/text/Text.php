<?php
namespace ITRocks\Framework\Layout\Structure\Field;

use ITRocks\Framework\Layout\Structure\Element\Has_Init;
use ITRocks\Framework\Layout\Structure\Field;

/**
 * Text field : here to display text
 */
class Text extends Field implements Has_Init
{

	//----------------------------------------------------------------------------------------- $text
	/**
	 * @var string
	 */
	public $text;

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
			$this->height = strlen($this->text)
				? ($this->font_size * (substr_count($this->text, LF) + 1))
				: 0;
		}
	}

}
