<?php
namespace SAF\Framework;
use AopJoinpoint;

abstract class Html_Translator
{

	//-------------------------------------------------------------------------------------- register
	/**
	 * Registers translation of [terms] in HTML templates
	 */
	public static function register()
	{
		aop_add_after(
			__NAMESPACE__ . "\\Html_Template->parse()",
			array(__CLASS__, "translatePage")
		);
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
		$i = 0;
		while (($i = strpos($content, "[", $i)) !== false) {
			$i ++;
			$j = strpos($content, "]", $i);
			if ($j > $i) {
				$text = substr($content, $i, $j - $i);
				$translation = Loc::tr($text);
				$content = substr($content, 0, $i - 1) . $translation . substr($content, $j + 1);
			}
		}
		$joinpoint->setReturnedValue($content);
	}

}
