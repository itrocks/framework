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

	//-------------------------------------------------------------------------------------- register
	/**
	 * Registers translation of [terms] in HTML templates
	 *
	 * @param $register Register
	 */
	public function register(Register $register)
	{
		$aop = $register->aop;
		$aop->afterMethod(
			[Template::class, 'parse'], [$this, 'translatePage']
		);
		$aop->beforeMethod(
			[Template::class, 'parseString'], [$this, 'translateString']
		);
		$aop->afterMethod(
			[Dom\Option::class, 'getContent'], [$this, 'translateOptionContent']
		);
	}

	//------------------------------------------------------------------------------ translateContent
	/**
	 * Translate a content in a context
	 *
	 * @param $content string
	 * @param $context string
	 * @return string
	 */
	public function translateContent(&$content, $context)
	{
		$i = 0;
		while (($i = strpos($content, PIPE, $i)) !== false) {
			$i ++;
			if ($i < strlen($content)) {
				if ($content[$i] == PIPE) {
					$content = substr($content, 0, $i) . substr($content, $i + 1);
				}
				elseif (!in_array($content[$i], [SP, CR, LF, TAB])) {
					$this->translateElement($content, $i, $context);
				}
			}
		}
		return $content;
	}

	//------------------------------------------------------------------------------ translateElement
	/**
	 * Translate a term from an html pages
	 *
	 * @param $content string
	 * @param $i       integer
	 * @param $context string
	 */
	private function translateElement(&$content, &$i, $context)
	{
		$j = strpos($content, PIPE, $i);
		if ($j >= $i) {
			$text        = substr($content, $i, $j - $i);
			$translation = Loc::tr($text, $context);
			$content     = substr($content, 0, $i - 1) . $translation . substr($content, $j + 1);
			$i += strlen($translation) - 1;
		}
	}

	//------------------------------------------------------------------------ translateOptionContent
	/**
	 * Translate content of html options in Html_Option objects
	 *
	 * @param $result string
	 */
	public function translateOptionContent(&$result)
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
	public function translatePage(Template $object, $result)
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
	public function translateString(Template $object, &$property_name)
	{
		$this->translateContent($property_name, $object->context());
	}

}
