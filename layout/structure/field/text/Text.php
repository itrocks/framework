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

	//------------------------------------------------------------------------------------------ init
	public function init()
	{
		if ($this->font_size && !$this->height) {
			$this->height = $this->font_size;
		}
	}

}
