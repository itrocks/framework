<?php
namespace SAF\Framework;
use AopJoinpoint;

class Wiki implements Plugin
{

	//------------------------------------------------------------------------------ $dont_parse_wiki
	/**
	 * When > 0, wiki will not be parsed (inside html form components)
	 *
	 * @var integer
	 */
	private static $dont_parse_wiki = 0;

	//-------------------------------------------------------------------------------- $geshi_replace
	/**
	 * geshi parsing replaces `code ... `code by `#1` ̀#2` etc.
	 * after geshi parsing, these specific codes will be replaced with geshi replacement
	 *
	 * @var string[] indice is the replacement code ̀#1̀, value is the geshi parsed code
	 */
	private $geshi_replace = array();

	//----------------------------------------------------------------------------------------- geshi
	/**
	 * Parse source code using GeSHi
	 *
	 * Source code is delimited between those full lines :
	 * `language
	 * ... code here
	 * `language
	 *
	 * It is replaced by a geshi replacement code like ̀#1` that will be solved later by geshiSolve()
	 * or now if $solve is true (default)
	 *
	 * @param $string string
	 * @param $solve  boolean
	 * @return string
	 */
	public function geshi($string, $solve = true)
	{
		$count = count($this->geshi_replace);
		$i = 0;
		while (($i < strlen($string)) && (($i = strpos($string, "`", $i)) !== false)) {
			$i ++;
			$j = strpos($string, "\n", $i);
			if ($j !== false) {
				$language = substr($string, $i, $j - $i);
				$cr = strpos($language, "\r") ? "\r" : "";
				$length = strlen($language);
				$k = strpos($string . $cr . "\n", "`" . $language . "\n", $j + $length);
				if ($k !== false) {
					$geshi = GeSHi::parse(substr($string, $j, $k - $j), str_replace("\r", "", $language));
					$replacement = "`#" . (++$count) . "`";
					$this->geshi_replace[$replacement] = $geshi;
					$k += $length + 2;
					$string = substr($string, 0, $i - 1) . $replacement . $cr . "\n" . substr($string, $k);
					$i += strlen($geshi);
				}
			}
		}
		if ($solve) {
			$string = $this->geshiSolve($string);
		}
		return $string;
	}

	//------------------------------------------------------------------------------------ geshiSolve
	/**
	 * Solve geshi replacements
	 *
	 * @param $string string
	 * @return string
	 */
	public function geshiSolve($string)
	{
		foreach ($this->geshi_replace as $replacement => $geshi) {
			$string = str_replace("<p>" . $replacement . "</p>", $geshi, $string);
		}
		return $string;
	}

	//----------------------------------------------------------------------------------- noParseZone
	/**
	 * @param $joinpoint AopJoinpoint
	 */
	public static function noParseZone(AopJoinpoint $joinpoint)
	{
		$varname = $joinpoint->getArguments()[1];
		$is_include = substr($varname, 0, 1) == "/";
		if (!$is_include) {
			self::$dont_parse_wiki ++;
		}
		$joinpoint->process();
		if (!$is_include) {
			self::$dont_parse_wiki --;
		}
	}

	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		Aop::add("around",
			'SAF\Framework\Html_Edit_Template->parseValue()',
			array(__CLASS__, "noParseZone")
		);
		Aop::add("after",
			'SAF\Framework\Reflection_Property_View->formatString()',
			array(__CLASS__, "stringWiki")
		);
	}

	//--------------------------------------------------------------------------- stringMultilineWiki
	/**
	 * Add wiki to strings
	 *
	 * @param $joinpoint AopJoinpoint
	 */
	public static function stringWiki(AopJoinpoint $joinpoint)
	{
		if (!static::$dont_parse_wiki) {
			/** @var $property Reflection_Property */
			$property = $joinpoint->getObject()->property;
			if ($property->getAnnotation("multiline")->value) {
				$value = $joinpoint->getReturnedValue();
				$wiki = new Wiki();
				$value = $wiki->geshi($value, false);
				$value = $wiki->textile($value);
				$value = $wiki->geshiSolve($value);
				$joinpoint->setReturnedValue($value);
			}
		}
	}

	//--------------------------------------------------------------------------------------- textile
	/**
	 * Parse a string using textile
	 *
	 * @param $string string
	 * @return string
	 */
	public function textile($string)
	{
		return (new Textile())->TextileThis($string);
	}

}
