<?php
namespace Framework;

class Html_Translator
{

	//--------------------------------------------------------------------------------- translatePage
	/**
	 * Send session ID as a POST var
	 * This is done at end of html templates parsing 
	 *
	 * @param AopJoinpoint $joinpoint
	 */
	public static function translatePage($joinpoint)
	{
		$content = $joinpoint->getReturnedValue();
		$i = 0;
		while (($i = strpos($content, "[", $i)) !== false) {
			$i ++;
			$j = strpos($content, "]", $i);
			$text = substr($content, $i, $j - $i);
			$translation = $text;
			$content = substr($content, 0, $i - 1) . $translation . substr($content, $j + 1);
		}
		$joinpoint->setReturnedValue($content);
	}

}

Aop::registerAfter("Framework\\Html_Template->parse()", "Framework\\Html_Translator::translatePage");
