<?php
namespace ITRocks\Framework\Tools;

use ITRocks\Framework\AOP\Joinpoint\Around_Method;
use ITRocks\Framework\Feature\Edit\Html_Template;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\Reflection\Reflection_Property_View;
use ITRocks\Framework\Tools\Wiki\GeSHi;
use ITRocks\Framework\Tools\Wiki\Textile;

/**
 * The wiki plugin enable wiki parsing of multiline properties values
 *
 * @feature Enable wiki formatting of descriptive texts using the Textile markup language
 */
class Wiki implements Registerable
{

	//------------------------------------------------------------------------------ $dont_parse_wiki
	/**
	 * When > 0, wiki will not be parsed (inside html form components)
	 *
	 * @var integer
	 */
	private int $dont_parse_wiki = 0;

	//-------------------------------------------------------------------------------- $geshi_replace
	/**
	 * geshi parsing replaces @language ... @ by `#1` `#2` etc.
	 * after geshi parsing, these specific codes will be replaced with geshi replacement
	 *
	 * @var string[] key is the replacement code `#1`, value is the geshi parsed code
	 */
	private array $geshi_replace = [];

	//----------------------------------------------------------------------------------------- geshi
	/**
	 * Parse source code using GeSHi
	 *
	 * Source code is delimited between those full lines :
	 * | @language
	 * | ... code here
	 * | @
	 *
	 * It is replaced by a geshi replacement code like `#1` that will be solved later by geshiSolve()
	 * or now if $solve is true (default)
	 *
	 * @param $string string
	 * @param $solve  boolean
	 * @return string
	 */
	public function geshi(string $string, bool $solve = true) : string
	{
		$lf    = LF;
		$count = count($this->geshi_replace);
		$i     = 0;
		while (($i < strlen($string)) && (($i = strpos(LF . $string, LF . AT, $i)) !== false)) {
			$i ++;
			$j = strpos($string, $lf, $i);
			if (($j !== false) && ($j < strpos($string, SP, $i))) {
				$language = substr($string, $i, $j - $i);
				if (trim($language) && !str_contains($language, AT)) {
					$cr = strpos($language, CR) ? CR : '';
					$k  = strpos($string . $cr . $lf, $lf . AT . $cr . $lf, $j);
					if ($k !== false) {
						$k++;
						$content = substr($string, $j + 1, $k - $j - 2 - strlen($cr));
						$content = str_replace(
							['&lt;', '&gt;', '&#123;', '&#125;'],
							['<',    '>',    '{',      '}',    ],
							$content
						);
						$geshi       = GeSHi::parse($content, $cr ? substr($language, 0, -1) : $language);
						$replacement = BQ . '#' . (++$count) . BQ;
						$this->geshi_replace[$replacement] = $geshi;
						$k     += strlen($cr) + 2;
						$string = substr($string, 0, $i - 1) . $replacement . $cr . $lf . substr($string, $k);
						$i     += strlen($replacement . $cr) - 1;
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
	public function geshiSolve(string $string) : string
	{
		foreach ($this->geshi_replace as $replacement => $geshi) {
			$string = str_replace(
				$replacement,
				strReplace(['{' => '&#123;', '}' => '&#125;'], $geshi),
				$string
			);
		}
		return $string;
	}

	//----------------------------------------------------------------------------------- noParseZone
	/**
	 * @output $joinpoint->result string value after reading value or exec specs (can be an object)
	 * @param $var_name  string can be an unique var or path.of.vars
	 * @param $joinpoint Around_Method
	 */
	public function noParseZone(string $var_name, Around_Method $joinpoint)
	{
		$is_include = str_starts_with($var_name, SL);
		if (!$is_include) {
			$this->dont_parse_wiki ++;
		}
		$joinpoint->result = $joinpoint->process();
		if (!$is_include) {
			$this->dont_parse_wiki --;
		}
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Register
	 */
	public function register(Register $register) : void
	{
		$aop = $register->aop;
		$aop->afterMethod ([Reflection_Property_View::class, 'formatValue'], [$this, 'stringWiki']);
		$aop->aroundMethod([Html_Template::class, 'parseValue'],             [$this, 'noParseZone']);
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
			if (isset($property->getAnnotation('geshi')->value)) {
				$programming_language = $property->getAnnotation('geshi')->value ?: 'php';
				if ($programming_language === 'auto') {
					$programming_language = str_contains($result, '<?php') ? 'php' : 'html';
				}
				$wiki   = new Wiki();
				$result = $wiki->geshi('@' . $programming_language . LF . $result . LF . '@');
			}
			if ($property->getAnnotation('textile')->value) {
				$wiki   = new Wiki();
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
		return (new Textile)->parse($string);
	}

}
