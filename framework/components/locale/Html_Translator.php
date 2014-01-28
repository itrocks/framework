<?php
namespace SAF\Framework;

/**
 * Html translator plugin : translates "|non-translated text|" from html pages to "translated text"
 */
abstract class Html_Translator implements Plugin
{

	//-------------------------------------------------------------------------------------- register
	/**
	 * Registers translation of [terms] in HTML templates
	 */
	public static function register()
	{
		Aop::addAfterMethodCall(
			array('SAF\Framework\Html_Template', "parse"), array(__CLASS__, "translatePage")
		);
		Aop::addBeforeMethodCall(
			array('SAF\Framework\Html_Template', "parseString"), array(__CLASS__, "translateString")
		);
		Aop::addBeforeMethodCall(
			array('SAF\Framework\Html_Option', "setContent"), array(__CLASS__, "translateOptionContent")
		);
	}

	//------------------------------------------------------------------------------ translateContent
	/**
	 * Translate a content in a context
	 * @param $content  string
	 * @param $context  string
	 * @return string
	 */
	public static function translateContent(&$content, $context)
	{
		$i = 0;
		while (($i = strpos($content, "|", $i)) !== false) {
			$i++;
			if (($i < strlen($content)) && (!in_array($content[$i], array(" ", "\n", "\r", "\t")))) {
				self::translateElement($content, $i, $context);
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
	public static function translateElement(&$content, &$i, $context)
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
	public static function translateOptionContent(&$content)
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
	public static function translatePage(Html_Template $object, $result)
	{
		return self::translateContent($result, get_class($object->getRootObject()));
	}

	//--------------------------------------------------------------------------------- translatePage
	/**
	 * Translate string.
	 *
	 * @param $object        Html_Template
	 * @param $property_name string
	 */
	public static function translateString(Html_Template $object, &$property_name)
	{
		self::translateContent($property_name, get_class($object->getRootObject()));
	}

}
