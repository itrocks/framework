<?php
namespace ITRocks\Framework\Layout\Structure\Field\Text;

use ITRocks\Framework\Layout\Structure\Field\Text;

/**
 * Formatted text feature
 *
 * @extends Text
 * @see Text
 */
trait Formatted
{

	//------------------------------------------------------------------------------------ formatText
	/**
	 * Format test typed into a Wysiwyg editor for printing :
	 *
	 * - replaces <p> by <br>, as outputs may create big paragraphs separators
	 * - replaces <p><br></p> by single <br>, for the same reason
	 * - ignore LF : treat them as spaces
	 * - remove useless CR
	 * - keep the global encapsulation into <p>...</p>, to keep detecting HTML text (see isFormatted)
	 *
	 * @return string
	 */
	public function formatTextForPrint() : string
	{
		if (!$this->isFormatted()) {
			return str_replace(CR, '', $this->text);
		}
		$text = str_replace([CR, '<p><br></p>'], ['', '<br>'], $this->text);
		$text = str_replace(['<p>', '</p>', LF], ['', '<br>', ' '], $text);
		if (substr($text, -4) === '<br>') {
			$text = substr($text, 0, -4);
		}
		return '<p>' . $text . '</p>';
	}

	//----------------------------------------------------------------------------------- isFormatted
	/**
	 * @return boolean
	 */
	public function isFormatted() : bool
	{
		return str_starts_with($this->text, '<p>') && str_ends_with($this->text, '</p>');
	}

}
