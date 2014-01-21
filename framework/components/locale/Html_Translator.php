<?php
namespace SAF\Framework;

use AopJoinpoint;

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
		Aop::add(Aop::AFTER,
			'SAF\Framework\Html_Template->parse()',
			array(__CLASS__, "translatePage")
		);
		Aop::add(Aop::BEFORE,
			'SAF\Framework\Html_Template->parseString()',
			array(__CLASS__, "translateString")
		);
		Aop::add(Aop::BEFORE,
			'SAF\Framework\Html_Option->setContent()',
			array(__CLASS__, "translateOptionContent")
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
	 * @param $joinpoint AopJoinpoint
	 */
	public static function translateOptionContent(AopJoinpoint $joinpoint)
	{
		$arguments = $joinpoint->getArguments();
		if (trim($arguments[0])) {
			$arguments[0] = Loc::tr($arguments[0]);
			$joinpoint->setArguments($arguments);
		}
	}

	//--------------------------------------------------------------------------------- translatePage
	/**
	 * Translate terms from html pages
	 * This is done at end of html templates parsing
	 *
	 * @param $joinpoint AopJoinpoint
	 */
	public static function translatePage(AopJoinpoint $joinpoint)
	{
		$content = $joinpoint->getReturnedValue();
		$context = get_class($joinpoint->getObject());
		$joinpoint->setReturnedValue(self::translateContent($content, $context));
	}

	//--------------------------------------------------------------------------------- translatePage
	/**
	 * Translate string.
	 *
	 * @param $joinpoint AopJoinpoint
	 */
	public static function translateString(AopJoinpoint $joinpoint)
	{
		$arguments = $joinpoint->getArguments();
		$content = $arguments[1];
		$context = get_class($joinpoint->getObject());
		$arguments[1] = self::translateContent($content, $context);
		$joinpoint->setArguments($arguments);
	}

}
