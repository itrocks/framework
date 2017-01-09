<?php
namespace ITRocks\Framework\Locale;

use ITRocks\Framework\AOP\Joinpoint\Around_Method;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;

/**
 * Compose translations with dynamic elements with separated translations
 *
 * Enables to prefer multiple translations of single words instead of big sentences translations
 * Enables translations to sort words in another order than original language
 *
 * @example
 * 'A text' is a simple translation string, directly translated without particular work
 * '¦Sales orders¦ list' will be dynamically translated : first 'Sales orders', then '$1 list'
 * '¦Sales¦ ¦orders¦ list' will translate 'Sales' then 'orders' then '$1 $2 list'
 */
class Translation_String_Composer implements Registerable
{

	//------------------------------------------------------------------------------------- holePipes
	/**
	 * Replaces ¦some sub-sentences¦ by $1, $2, etc. into text.
	 * Returns the translations of those sub-sentences.
	 *
	 * Must be called after ignore (!!) and ignoreTags (<tag>) to allow ¦some !ignore!¦
	 * and ¦some <tagged>¦ to work
	 *
	 * @param $text       string
	 * @param $translator Translator
	 * @param $context    string
	 * @return string[] key is $1, $2, $3, until $F. Value is the matching translated text.
	 */
	protected function holePipes(&$text, Translator $translator, $context)
	{
		$elements = [];
		$i        = 0;
		$number   = 0;
		while (($i = strpos($text, '¦', $i)) !== false) {
			$i += 2;
			$j  = strpos($text, '¦', $i);
			if ($j >= $i) {
				$hex_number = dechex(++$number);
				$elements['$' . $hex_number] = $translator->translate(substr($text, $i, $j - $i), $context);
				$text = substr($text, 0, $i - 2) . '$' . $hex_number . substr($text, $j + 2);
				$i   += strlen($hex_number) - 1;
			}
		}
		return $elements;
	}

	//---------------------------------------------------------------------------------------- ignore
	/**
	 * Replaces !some sub-sentences! by $!1, $!2, etc. into text.
	 * Returns the original non-translated values of those sub-sentences.
	 *
	 * When !<tag>, the ending limit of the sub-sentence will be the next >!
	 *
	 * Must be called before ignoreTags(), as !<tag>some things</tag>!
	 * must be replaced by ['$!1' => '<tag>some things</tag>']
	 * and not by ['$<1' => '<tag>', '$<2' => '</tag>', '$!1' => '$<1some things$<2']
	 *
	 * @param $text string
	 * @return string[] key is $!1, $!2, $!3, ... until $!F. Value is the matching text.
	 */
	protected function ignore(&$text)
	{
		$elements = [];
		$i        = 0;
		$number   = 0;
		while (($i = strpos($text, '!', $i)) !== false) {
			$i ++;
			$j = strpos($text, '!', $i);
			if (($j > $i) && (strpos(SP . TAB . CR . LF, $text[$i]) === false)) {
				$hex_number = '!' . dechex(++$number);
				$elements['$' . $hex_number] = substr($text, $i, $j - $i);
				$text = substr($text, 0, $i - 1) . '$' . $hex_number . substr($text, $j + 1);
				$i   += strlen($hex_number) - 1;
			}
		}
		return $elements;
	}

	//------------------------------------------------------------------------------------ ignoreTags
	/**
	 * Replaces <tags> by $<1, $<2, etc. into text.
	 * Returns the original non-translated tags.
	 *
	 * Must be called after ignore(), as !<tag>some things</tag>!
	 * must be replaced by ['$!1' => '<tag>some things</tag>']
	 * and not by ['$<1' => '<tag>', '$<2' => '</tag>', '$!1' => '$<1some things$<2']
	 *
	 * @param $text string
	 * @return string[] key is $<1, $<2, $<3, ... until $<F. Value is the matching tag.
	 */
	protected function ignoreTags(&$text)
	{
		$elements     = [];
		$i            = 0;
		$open_number  = 0;
		$close_number = 0;
		while (($i = strpos($text, '<', $i)) !== false) {
			$i ++;
			if (ctype_alpha($text[$i]) || ($text[$i] === SL)) {
				$j = strpos($text, '>', $i);
				if (($j > $i) && (strpos(SP . TAB . CR . LF, $text[$i]) === false)) {
					$hex_number = (($text[$i] === SL) ? '>' : '<')
						. dechex(($text[$i] === SL) ? ++$close_number : ++$open_number);
					$elements['$' . $hex_number] = substr($text, $i - 1, $j - $i + 2);
					$text = substr($text, 0, $i - 1) . '$' . $hex_number . substr($text, $j + 1);
					$i   += strlen($hex_number) - 1;
				}
			}
		}
		return $elements;
	}

	//----------------------------------------------------------------------------------- onTranslate
	/**
	 * @param $text      string
	 * @param $object    Translator
	 * @param $context   string
	 * @param $joinpoint Around_Method
	 * @return string
	 */
	public function onTranslate($text, Translator $object, $context, Around_Method $joinpoint)
	{
		static $top_call = true;
		$context = isset($context) ? $context : '';
		if (
			$top_call
			&& (
				(strpos($text, '¦') !== false)
				|| (strpos($text, '!') !== false)
				|| (strpos($text, '<') !== false)
			)
		) {
			$capital         = strIsCapitals($text[0]);
			$translator      = $object;

			$top_call        = false;
			$ignore_elements = $this->ignore($text);
			$ignore_tags     = $this->ignoreTags($text);
			$sub_elements    = $this->holePipes($text, $translator, $context);
			$translation     = $translator->translate($text, $context);
			$top_call        = true;
			$translation     = str_replace(array_keys($sub_elements),    $sub_elements,    $translation);
			$translation     = str_replace(array_keys($ignore_tags),     $ignore_tags,     $translation);
			$translation     = str_replace(array_keys($ignore_elements), $ignore_elements, $translation);

			// this makes sure the first letter is a capital, even if at any step the text begun with '$'
			if ($capital) {
				$translation = ucfirsta($translation);
			}
		}
		else {
			$translation = $joinpoint->process($text, $context);
		}
		return $translation;
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Register
	 */
	public function register(Register $register)
	{
		$register->aop->aroundMethod([Translator::class, 'translate'], [$this, 'onTranslate']);
	}

}
