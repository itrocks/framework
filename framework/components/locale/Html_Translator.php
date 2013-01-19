<?php
namespace SAF\Framework;
use AopJoinpoint;

abstract class Html_Translator implements Plugin
{

	//-------------------------------------------------------------------------------------- register
	/**
	 * Registers translation of [terms] in HTML templates
	 */
	public static function register()
	{
		Aop::add("after",
			__NAMESPACE__ . "\\Html_Template->parse()",
			array(__CLASS__, "translatePage")
		);
	}

	//------------------------------------------------------------------------------ translateElement
	/**
	 * Translate a term from an html pages
	 *
	 * @param string $content
	 * @param integer $i
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

	//--------------------------------------------------------------------------------- translatePage
	/**
	 * Translate terms from html pages
	 * This is done at end of html templates parsing
	 *
	 * @param AopJoinpoint $joinpoint
	 */
	public static function translatePage(AopJoinpoint $joinpoint)
	{
		$content = $joinpoint->getReturnedValue();
		$context = get_class($joinpoint->getObject());
		$i = 0;
		while (($i = strpos($content, "|", $i)) !== false) {
			$i++;
			if (($i < strlen($content)) && (!in_array($content[$i], array(" ", "\n", "\r", "\t")))) {
				self::translateElement($content, $i, $context);
			}
		}
		$joinpoint->setReturnedValue($content);
	}

}
