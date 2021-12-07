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
		$text = str_replace([CR, P . BR . _P], ['', BR], $this->text);
		$text = str_replace([P, _P, LF, '</ol>'], ['', BR, SP, '</ol><br>'], $text);
		if (substr($text, -4) === BR) {
			$text = substr($text, 0, -4);
		}
		return P . $this->toUnorderedLists($text) . _P;
	}

	//----------------------------------------------------------------------------------- isFormatted
	/**
	 * @return boolean
	 */
	public function isFormatted() : bool
	{
		return str_starts_with($this->text, P) && str_ends_with($this->text, _P);
	}

	//------------------------------------------------------------------------------ toUnorderedLists
	/**
	 * @param $text string
	 * @return string
	 */
	protected function toUnorderedLists(string $text) : string
	{
		$index     = -1;
		$depth     = 0;
		$unordered = [];
		while (($index = strpos($text, '<', $index + 1)) !== false) {
			if (substr($text, $index, 4) === '<ol>') {
				$unordered[$depth] = (substr($text, $index, 27) === '<ol><li data-list="bullet">');
				if ($unordered[$depth]) {
					$text[$index + 1]  = 'u';
				}
				$depth ++;
			}
			if (substr($text, $index, 5) === '</ol>') {
				$depth --;
				if ($unordered[$depth]) {
					$text[$index + 2] = 'u';
				}
			}
		}
		return $text;
	}

}
