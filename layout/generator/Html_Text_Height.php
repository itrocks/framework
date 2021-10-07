<?php
namespace ITRocks\Framework\Layout\Generator;

use ITRocks\Framework\Layout\Output;
use ITRocks\Framework\Layout\Structure\Element;
use ITRocks\Framework\Layout\Structure\Field\Text;

/**
 * Resize elements and iterations which contain HTML text
 */
class Html_Text_Height extends Shift_Top
{

	//--------------------------------------------------------------------------------------- $output
	/**
	 * @var Output
	 */
	protected Output $output;

	//--------------------------------------------------------------------------------------- element
	/**
	 * @param $element Element
	 * @return float The height increase
	 */
	protected function element(Element $element) : float
	{
		if (!(($element instanceof Text) && $element->isFormatted())) {
			return 0;
		}
		$element->text = $element->formatTextForPrint();
		$output_height = $this->output->htmlHeight(
			$element->text, $element->width, $element->font_size
		);
		if ($output_height > $element->height) {
			$element_height  = $element->height;
			$element->height = $output_height;
			return $element->height - $element_height;
		}
		return 0;
	}

}
