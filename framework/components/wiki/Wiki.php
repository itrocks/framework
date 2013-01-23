<?php
namespace SAF\Framework;
use AopJoinpoint;
use Textile;

abstract class Wiki implements Plugin
{

	//------------------------------------------------------------------------------ $dont_parse_wiki
	/**
	 * When > 0, wiki will not be parsed (inside html form components)
	 *
	 * @var integer
	 */
	private static $dont_parse_wiki = 0;

	//----------------------------------------------------------------------------------- noParseZone
	/**
	 * @param AopJoinpoint $joinpoint
	 */
	public static function noParseZone(AopJoinpoint $joinpoint)
	{
		self::$dont_parse_wiki ++;
		$joinpoint->process();
		self::$dont_parse_wiki --;
	}

	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		Aop::add("around",
			__NAMESPACE__ . "\\Html_Edit_Template->parseValue()",
			array(__CLASS__, "noParseZone")
		);
		Aop::add("after",
			__NAMESPACE__ . "\\Reflection_Property_View->formatString()",
			array(__CLASS__, "stringWiki")
		);
	}

	//--------------------------------------------------------------------------- stringMultilineWiki
	/**
	 * Add wiki to strings
	 *
	 * @param AopJoinpoint $joinpoint
	 */
	public static function stringWiki(AopJoinpoint $joinpoint)
	{
		if (!static::$dont_parse_wiki) {
			$property = $joinpoint->getObject()->property;
			if ($property->getAnnotation("multiline")->value) {
				$joinpoint->setReturnedValue(self::textile($joinpoint->getReturnedValue()));
			}
		}
	}

	//--------------------------------------------------------------------------------------- textile
	public static function textile($string)
	{
		return (new Textile())->TextileThis($string);
	}

}
