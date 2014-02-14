<?php
namespace SAF\Framework;

use SAF\Plugins;

/**
 * Html translator plugin : translates "|non-translated text|" from html pages to "translated text"
 */
class Html_Translator implements Plugins\Registerable
{

	//-------------------------------------------------------------------------------------- register
	/**
	 * Registers translation of [terms] in HTML templates
	 *
	 * @param $register Plugins\Register
	 */
	public function register(Plugins\Register $register)
	{
		$aop = $register->aop;
		$aop->afterMethod(
			array('SAF\Framework\Html_Template', "parse"), array($this, "translatePage")
		);
		$aop->beforeMethod(
			array('SAF\Framework\Html_Template', "parseString"), array($this, "translateString")
		);
		$aop->beforeMethod(
			array('SAF\Framework\Html_Option', "setContent"), array($this, "translateOptionContent")
		);
	}

	//------------------------------------------------------------------------------ translateContent
	/**
	 * Translate a content in a context
	 * @param $content  string
	 * @param $context  string
	 * @return string
	 */
	public function translateContent(&$content, $context)
	{
		$i = 0;
		while (($i = strpos($content, "|", $i)) !== false) {
			$i++;
			if (($i < strlen($content)) && (!in_array($content[$i], array(" ", "\n", "\r", "\t")))) {
				$this->translateElement($content, $i, $context);
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
	public function translateElement(&$content, &$i, $context)
	{
		$j = strpos($content, "|", $i);
		if ($j >= $i) {
			$text = substr($content, $i, $j - $i);
			$translation = Loc::tr($text, $context);
			$content = substr($content, 0, $i - 1) . $translation . substr($content, $j + 1);
			$i += strlen($translation) - 1;
		}
	}

	//------------------------------------------------------------------------ translateOptionContent
	/**
	 * Translate content of html options in Html_Option objects
	 *
	 * @param $content string
	 */
	public function translateOptionContent(&$content)
	{
		if (trim($content)) {
			$content = Loc::tr($content);
		}
	}

	//--------------------------------------------------------------------------------- translatePage
	/**
	 * Translate terms from html pages
	 * This is done at end of html templates parsing
	 *
	 * @param $object Html_Template
	 * @param $result string
	 * @return string
	 */
	public function translatePage(Html_Template $object, $result)
	{
		return $this->translateContent($result, get_class($object));
	}

	//--------------------------------------------------------------------------------- translatePage
	/**
	 * Translate string.
	 *
	 * @param $object        Html_Template
	 * @param $property_name string
	 */
	public function translateString(Html_Template $object, &$property_name)
	{
		$this->translateContent($property_name, get_class($object));
	}

}
