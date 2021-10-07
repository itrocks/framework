<?php
namespace ITRocks\Framework\Layout\Generator;

use ITRocks\Framework\Layout\Output;
use ITRocks\Framework\Layout\Structure\Element;
use ITRocks\Framework\Layout\Structure\Field\Final_Text;
use ITRocks\Framework\Layout\Structure\Field\Property;
use ITRocks\Framework\Layout\Structure\Field\Text;
use ITRocks\Framework\Layout\Structure\Has_Structure;

/**
 * Automatic line feed (aka carriage return)
 */
class Automatic_Line_Feed extends Shift_Top
{
	use Has_Structure;

	//--------------------------------------------------------------------------------------- $output
	/**
	 * @var Output
	 */
	protected $output;

	//--------------------------------------------------------------------------------------- element
	/**
	 * @param $element Element
	 * @return float The height increase
	 */
	protected function element(Element $element) : float
	{
		if (
			!($element instanceof Text\Resizable)
			&& !(($element instanceof Final_Text) && ($element->property instanceof Property\Resizable))
		) {
			return 0;
		}

		$changed_text  = false;
		$element_texts = explode(LF, str_replace(CR, '', $element->text));
		foreach ($element_texts as &$element_text) {
			$text_width = $this->output->textWidth($element_text, null, null, $element->font_size);
			if ($text_width > $element->width) {
				$line_width       = 0;
				$line_words_count = 0;
				$space_width      = $this->output->textWidth(SP, null, null, $element->font_size);
				$text             = '';
				foreach (explode(SP, $element_text) as $word) {
					$word_width = $this->output->textWidth($word, null, null, $element->font_size);
					if (
						(($line_width + ($line_words_count ? $space_width : 0) + $word_width) > $element->width)
						&& strlen($text)
					) {
						$text            .= LF;
						$line_width       = 0;
						$line_words_count = 0;
					}
					elseif ($line_words_count) {
						$text       .= SP;
						$line_width += $space_width;
					}
					$text             .= $word;
					$line_width       += $word_width;
					$line_words_count ++;
				}
				$changed_text = true;
				$element_text = $text;
			}
		}
		if ($changed_text) {
			$element_height = $element->height;
			$element->text  = join(LF, $element_texts);
			$element->calculateHeight();
			return $element->height - $element_height;
		}
		return 0;
	}

}
