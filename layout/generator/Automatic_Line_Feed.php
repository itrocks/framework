<?php
namespace ITRocks\Framework\Layout\Generator;

use ITRocks\Framework\Layout\Structure\Element;
use ITRocks\Framework\Layout\Structure\Field\Final_Text;
use ITRocks\Framework\Layout\Structure\Field\Property;
use ITRocks\Framework\Layout\Structure\Field\Text;
use ITRocks\Framework\Layout\Structure\Group;
use ITRocks\Framework\Layout\Structure\Group\Iteration;
use ITRocks\Framework\Layout\Structure\Has_Structure;

/**
 * Automatic line feed (aka carriage return)
 */
class Automatic_Line_Feed
{
	use Has_Output;
	use Has_Structure;

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

		if ($element->isFormatted()) {
			$element_height = $element->height;
			return $element->calculateHeight($this->output) - $element_height;
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
			return $element->calculateHeight() - $element_height;
		}
		return 0;
	}

	//----------------------------------------------------------------------------------------- group
	/**
	 * @param $group Group
	 */
	protected function group(Group $group) : void
	{
		foreach ($group->elements as $element) {
			$this->element($element);
		}
		foreach ($group->groups as $sub_group) {
			$this->group($sub_group);
		}
		foreach ($group->iterations as $iteration) {
			$this->iteration($iteration);
		}
	}

	//------------------------------------------------------------------------------------- iteration
	/**
	 * @param $iteration Iteration
	 */
	protected function iteration(Iteration $iteration) : void
	{
		$shift     = 0;
		$shift_top = 0;
		$top       = -1;
		foreach ($iteration->elements as $element) {
			if ($element->top > $top) {
				$shift_top += $shift;
				$shift      = 0;
				$top        = $element->top;
			}
			$element->top += $shift_top;
			$shift         = max($shift, $this->element($element));
		}
	}

	//------------------------------------------------------------------------------------------- run
	public function run() : void
	{
		foreach ($this->structure->pages as $page) {
			foreach ($page->elements as $element) {
				$this->element($element);
			}
			foreach ($page->groups as $group) {
				$this->group($group);
			}
		}
	}

}
