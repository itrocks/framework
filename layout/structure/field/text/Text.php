<?php
namespace ITRocks\Framework\Layout\Structure\Field;

use ITRocks\Framework\Layout\Structure\Field;

/**
 * Text field : here to display text
 */
class Text extends Field
{

	//----------------------------------------------------------------------------------------- $text
	/**
	 * @var string
	 */
	public $text;

	//------------------------------------------------------------------------------------------ init
	public function init()
	{
		if ($this->font_size && !$this->height) {
			$this->height = $this->font_size * (substr_count($this->text, LF) + 1);
		}
		parent::init();
	}

}
