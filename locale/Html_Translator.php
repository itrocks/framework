<?php
namespace ITRocks\Framework\Locale;

use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\View\Html\Dom;
use ITRocks\Framework\View\Html\Template;

/**
 * Html translator plugin : translates '|non-translated text|' from html pages to 'translated text'
 */
class Html_Translator implements Registerable
{

	//---------------------------------------------------------------------- doNotTranslateEmptyValue
	/**
	 * @param $content string
	 * @param $result  integer
	 */
	public function doNotTranslateEmptyValue(string &$content, int $result) : void
	{
		if (substr($content, $result - 2, 2) === PIPE . PIPE) {
			$content = substr($content, 0, $result - 2) . substr($content, $result);
		}
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * Registers translation of [terms] in HTML templates
	 *
	 * @param $register Register
	 */
	public function register(Register $register) : void
	{
		$aop = $register->aop;
		$aop->afterMethod ([Dom\Option::class, 'getContent'],  [$this, 'translateOptionContent']);
		$aop->afterMethod ([Template::class,   'parse'],       [$this, 'translatePage']);
		$aop->afterMethod ([Template::class,   'parseVar'],    [$this, 'doNotTranslateEmptyValue']);
		$aop->beforeMethod([Template::class,   'parseString'], [$this, 'translateString']);
	}

	//------------------------------------------------------------------------------ translateContent
	/**
	 * Translate a content in a context
	 *
	 * @param $content string
	 * @param $context string
	 * @return string
	 */
	public function translateContent(string &$content, string $context) : string
	{
		$position = 0;
		while (($position = strpos($content, PIPE, $position)) !== false) {
			$position ++;
			if ($position < strlen($content)) {
				if ($content[$position] === PIPE) {
					$content = substr($content, 0, $position) . substr($content, $position);
					$position ++;
				}
				elseif (!in_array($content[$position], [SP, CR, LF, TAB])) {
					$this->translateElement($content, $position, $context);
				}
			}
		}
		return $content;
	}

	//------------------------------------------------------------------------------ translateElement
	/**
	 * Translate a term from an html pages
	 *
	 * @param $content  string
	 * @param $position integer
	 * @param $context  string
	 */
	private function translateElement(string &$content, int &$position, string $context) : void
	{
		$next = strpos($content, PIPE, $position);
		if ($next >= $position) {
			$text        = substr($content, $position, $next - $position);
			$translation = Loc::tr($text, $context);
			$content   = substr($content, 0, $position - 1) . $translation . substr($content, $next + 1);
			$position += strlen($translation) - 1;
		}
	}

	//------------------------------------------------------------------------ translateOptionContent
	/**
	 * Translate content of html options in Html_Option objects
	 *
	 * @param $result string
	 */
	public function translateOptionContent(string &$result) : void
	{
		if (trim($result)) {
			$result = Loc::tr($result);
		}
	}

	//--------------------------------------------------------------------------------- translatePage
	/**
	 * Translate terms from html pages
	 * This is done at end of html templates parsing
	 *
	 * @param $object Template
	 * @param $result string
	 * @return string
	 */
	public function translatePage(Template $object, string $result) : string
	{
		return $this->translateContent($result, $object->context());
	}

	//------------------------------------------------------------------------------- translateString
	/**
	 * Translate string.
	 *
	 * @param $object        Template
	 * @param $property_name string
	 */
	public function translateString(Template $object, string &$property_name) : void
	{
		$this->translateContent($property_name, $object->context());
	}

}
