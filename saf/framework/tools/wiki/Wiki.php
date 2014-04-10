<?php
namespace SAF\Framework\Tools;

use SAF\Framework\AOP\Joinpoint\Around_Method;
use SAF\Framework\Plugin\Register;
use SAF\Framework\Plugin\Registerable;
use SAF\Framework\Reflection\Reflection_Property_View;
use SAF\Framework\Tools\Wiki\GeSHi;
use SAF\Framework\Tools\Wiki\Textile;
use SAF\Framework\Widget\Edit\Html_Template;

/**
 * The wiki plugin enable wiki parsing of multiline properties values
 */
class Wiki implements Registerable
{

	//------------------------------------------------------------------------------ $dont_parse_wiki
	/**
	 * When > 0, wiki will not be parsed (inside html form components)
	 *
	 * @var integer
	 */
	private $dont_parse_wiki = 0;

	//-------------------------------------------------------------------------------- $geshi_replace
	/**
	 * geshi parsing replaces @language ... @ by `#1` `#2` etc.
	 * after geshi parsing, these specific codes will be replaced with geshi replacement
	 *
	 * @var string[] indice is the replacement code `#1`, value is the geshi parsed code
	 */
	private $geshi_replace = [];

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
		$lf = LF;
		$count = count($this->geshi_replace);
		$i = 0;
		while (($i < strlen($string)) && (($i = strpos($string, AT, $i)) !== false)) {
			$i ++;
			$j = strpos($string, $lf, $i);
			if (($j !== false) && ($j < strpos($string, SP, $i))) {
				$language = substr($string, $i, $j - $i);
				if (trim($language) && !strpos($language, AT)) {
					$cr = strpos($language, CR) ? CR : '';
					$k = strpos($string . $cr . $lf, $lf . AT . $cr . $lf, $j);
					if ($k !== false) {
						$k++;
						$content = substr($string, $j + 1, $k - $j - 2 - strlen($cr));
						$content = str_replace(
							['&lt;', '&gt;', '&#123;', '&#125;'],
							['<',    '>',    '{',      '}'],
							$content
						);
						$geshi = GeSHi::parse($content, $cr ? substr($language, 0, -1) : $language);
						$replacement = BQ . '#' . (++$count) . BQ;
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
					['{',      '}'],
					['&#123;', '&#125;'],
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
	 * @param $joinpoint Around_Method
	 * @return string var value after reading value / executing specs (can be an object)
	 */
	public function noParseZone($var_name, Around_Method $joinpoint)
	{
		$is_include = (substr($var_name, 0, 1) == SL);
		if (!$is_include) {
			$this->dont_parse_wiki ++;
		}
		$result = $joinpoint->process();
		if (!$is_include) {
			$this->dont_parse_wiki --;
		}
		return $result;
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Register
	 */
	public function register(Register $register)
	{
		$aop = $register->aop;
		$aop->aroundMethod([Html_Template::class, 'parseValue'],         [$this, 'noParseZone']);
		$aop->afterMethod( [Reflection_Property_View::class, 'formatString'], [$this, 'stringWiki']);
	}

	//------------------------------------------------------------------------------------ stringWiki
	/**
	 * Add wiki to strings
	 *
	 * @param $object Reflection_Property_View
	 * @param $result string
	 */
	public function stringWiki(Reflection_Property_View $object, &$result)
	{
		if (!$this->dont_parse_wiki) {
			$property = $object->property;
			if ($property->getAnnotation('textile')->value) {
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
