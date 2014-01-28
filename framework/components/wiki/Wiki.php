<?php
namespace SAF\Framework;

/**
 * The wiki plugin enable wiki parsing of multiline properties values
 */
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
	 * geshi parsing replaces @language ... @ by `#1` `#2` etc.
	 * after geshi parsing, these specific codes will be replaced with geshi replacement
	 *
	 * @var string[] indice is the replacement code `#1`, value is the geshi parsed code
	 */
	private $geshi_replace = array();

	//----------------------------------------------------------------------------------------- geshi
	/**
	 * Parse source code using GeSHi
	 *
	 * Source code is delimited between those full lines :
	 * @language
	 * ... code here
	 * @
	 *
	 * It is replaced by a geshi replacement code like `#1` that will be solved later by geshiSolve()
	 * or now if $solve is true (default)
	 *
	 * @param $string string
	 * @param $solve  boolean
	 * @return string
	 */
	public function geshi($string, $solve = true)
	{
		$lf = "\n";
		$count = count($this->geshi_replace);
		$i = 0;
		while (($i < strlen($string)) && (($i = strpos($string, "@", $i)) !== false)) {
			$i ++;
			$j = strpos($string, $lf, $i);
			if (($j !== false) && ($j < strpos($string, " ", $i))) {
				$language = substr($string, $i, $j - $i);
				if (trim($language) && !strpos($language, "@")) {
					$cr = strpos($language, "\r") ? "\r" : "";
					$k = strpos($string . $cr . $lf, "$lf@$cr$lf", $j);
					if ($k !== false) {
						$k++;
						$content = substr($string, $j + 1, $k - $j - 2 - strlen($cr));
						$content = str_replace(
							array("&lt;", "&gt;", "&#123;", "&#125;"),
							array("<",    ">",    "{",      "}"),
							$content
						);
						$geshi = GeSHi::parse($content, $cr ? substr($language, 0, -1) : $language);
						$replacement = "`#" . (++$count) . "`";
						$this->geshi_replace[$replacement] = $geshi;
						$k += strlen($cr) + 2;
						$string = substr($string, 0, $i - 1) . $replacement . $cr . $lf . substr($string, $k);
						$i += strlen($replacement . $cr) - 1;
					}
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
			$string = str_replace(
				$replacement,
				str_replace(
					array("{",      "}"),
					array("&#123;", "&#125;"),
					$geshi
				),
				$string
			);
		}
		return $string;
	}

	//----------------------------------------------------------------------------------- noParseZone
	/**
	 * @param $var_name  string can be an unique var or path.of.vars
	 * @param $as_string boolean if true, returned value will always be a string
	 * @param $joinpoint Around_Method_Joinpoint
	 * @return string var value after reading value / executing specs (can be an object)
	 */
	public static function noParseZone($var_name, $as_string, Around_Method_Joinpoint $joinpoint)
	{
		$is_include = substr($var_name, 0, 1) == "/";
		if (!$is_include) {
			self::$dont_parse_wiki ++;
		}
		$result = $joinpoint->process($var_name, $as_string);
		if (!$is_include) {
			self::$dont_parse_wiki --;
		}
		return $result;
	}

	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		Aop::addAroundMethodCall(
			array('SAF\Framework\Html_Edit_Template', "parseValue"),
			array(__CLASS__, "noParseZone")
		);
		Aop::addAfterMethodCall(
			array('SAF\Framework\Reflection_Property_View', "formatString"),
			array(__CLASS__, "stringWiki")
		);
	}

	//------------------------------------------------------------------------------------ stringWiki
	/**
	 * Add wiki to strings
	 *
	 * @param $object Reflection_Property_View
	 * @param $result string
	 */
	public static function stringWiki(Reflection_Property_View $object, &$result)
	{
		if (!static::$dont_parse_wiki) {
			$property = $object->property;
			if ($property->getAnnotation("textile")->value) {
				$wiki = new Wiki();
				$result = $wiki->geshi($result, false);
				$result = $wiki->textile($result);
				$result = $wiki->geshiSolve($result);
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
		return Textile::parse($string);
	}

}
