<?php
namespace ITRocks\Framework\Tools;

use DateTime;
use ITRocks\Framework\Builder;

/**
 * A String class to get commonly used string features into an object
 */
class String_Class
{

	//---------------------------------------------------------------------------------------- $value
	/**
	 * @var string
	 */
	public $value;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value string
	 */
	public function __construct($value = '')
	{
		$this->value = $value;
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return strval($this->value);
	}

	//------------------------------------------------------------------------------------------- abs
	/**
	 * @return float|int
	 */
	public function abs()
	{
		return abs($this->value);
	}

	//------------------------------------------------------------------------------------- cleanWord
	/**
	 * Clean the word, this delete all character who don't have a place in a current word.
	 *
	 * @example
	 * cleanWord('Albert, ') => return 'Albert'
	 * cleanWord(' list : ') => return 'list'
	 * @return String_Class the clean word.
	 * @todo see if there is any conceptual difference with strSimplify. If not, replace it !
	 */
	function cleanWord()
	{
		return new String_Class(
			preg_replace('#[^a-zA-Zàáâãäåçèéêëìíîïðòóôõöùúûüýÿ_\-\'\\\/]#', '', $this->value)
		);
	}

	//--------------------------------------------------------------------------------------- display
	/**
	 * @return string
	 */
	public function display()
	{
		return str_replace('_', SP, $this->value);
	}

	//---------------------------------------------------------------------------------------- escape
	/**
	 * @return String_Class
	 */
	public function escape()
	{
		return new String_Class(str_replace(BS, BS . BS, $this->value));
	}

	//----------------------------------------------------------------------------------------- first
	/**
	 * First element of a separated string
	 *
	 * @return String_Class
	 */
	public function first()
	{
		foreach ([':', DOT, '-', ','] as $char) {
			if (str_contains($this->value, $char)) {
				return new String_Class(substr($this->value, 0, strpos($this->value, $char)));
			}
		}
		return new String_Class($this->value);
	}

	//----------------------------------------------------------------------------------------- geshi
	/**
	 * Parse with geshi
	 *
	 * @param $programming_language string
	 * @return String_Class
	 */
	public function geshi($programming_language = 'php')
	{
		$wiki = new Wiki();
		if ($programming_language === 'php' && !str_contains($this->value, '<?php')) {
			$programming_language = 'html';
		}
		$text = $wiki->geshi('@' . $programming_language . LF . $this->value . LF . '@');
		return new String_Class($text);
	}

	//---------------------------------------------------------------------------------- htmlEntities
	/**
	 * @return String_Class
	 */
	public function htmlEntities()
	{
		return new String_Class(htmlentities($this->value, ENT_QUOTES|ENT_HTML5));
	}

	//------------------------------------------------------------------------------ htmlSpecialChars
	/**
	 * @return static
	 */
	public function htmlSpecialChars() : static
	{
		return new String_Class(htmlspecialchars($this->value));
	}

	//----------------------------------------------------------------------------------------- idTag
	/**
	 * Format the value so that it can be used into an id="" HTML tag
	 *
	 * @return string
	 */
	public function idTag()
	{
		return str_replace([DOT, SL], '-', $this->uri());
	}

	//---------------------------------------------------------------------------------------- isWord
	/**
	 * Test is the string like a word
	 *
	 * @return integer 0 if it's not a word.
	 */
	public function isWord()
	{
		return preg_match('#[a-zA-Zàáâãäåçèéêëìíîïðòóôõöùúûüýÿ]#', $this->value);
	}

	//------------------------------------------------------------------------------------------ last
	/**
	 * Last element of a separated string
	 *
	 * @param $count integer
	 * @return String_Class
	 */
	public function last($count = 1)
	{
		foreach ([':', DOT, '-', ','] as $char) {
			if (strrpos($this->value, $char) !== false) {
				return new String_Class(rLastParse($this->value, $char, $count, true));
			}
		}
		return new String_Class($this->value);
	}

	//------------------------------------------------------------------------------------------ left
	/**
	 * @param $length integer
	 * @return string
	 */
	public function left(int $length)
	{
		return new String_Class(substr($this->value, 0, $length));
	}

	//---------------------------------------------------------------------------------------- length
	/**
	 * @return integer
	 */
	public function length()
	{
		return strlen($this->value);
	}

	//----------------------------------------------------------------------------------------- lower
	/**
	 * @return String_Class
	 */
	public function lower()
	{
		return new String_Class(strtolower($this->value));
	}

	//------------------------------------------------------------------------------------------ nbsp
	/**
	 * @return String_Class
	 */
	public function nbsp()
	{
		return empty($this->value) ? new String_Class('&nbsp;') : $this;
	}

	//-------------------------------------------------------------------------------------------- of
	/**
	 * Constructs a new String
	 *
	 * @param $string string
	 * @return self
	 */
	public static function of($string)
	{
		return new String_Class($string);
	}

	//------------------------------------------------------------------------------------------ path
	/**
	 * Changes a 'A\Class\Name' into 'A/Class/Name'
	 *
	 * @return String_Class
	 */
	public function path()
	{
		return new String_Class(str_replace(BS, SL, Builder::current()->sourceClassName($this->value)));
	}

	//----------------------------------------------------------------------------------------- right
	/**
	 * @param $length integer
	 * @return string
	 */
	public function right(int $length)
	{
		return new String_Class(substr($this->value, -$length));
	}

	//----------------------------------------------------------------------------------------- round
	/**
	 * @param $decimals integer
	 * @return float
	 */
	public function round(int $decimals = 0) : float
	{
		return round($this->value, $decimals);
	}

	//----------------------------------------------------------------------------------------- short
	/**
	 * @return String_Class
	 */
	public function short()
	{
		return new String_Class(Namespaces::shortClassName($this->value));
	}

	//------------------------------------------------------------------------------------------ sign
	/**
	 * @return string
	 */
	public function sign()
	{
		return (substr($this->value, 0, 1) === '-') ? '-' : '+';
	}

	//---------------------------------------------------------------------------------------- source
	/**
	 * Change a class name to a source class name
	 */
	public function source()
	{
		return new String_Class(Builder::current()->sourceClassName($this->value));
	}

	//---------------------------------------------------------------------------------------- substr
	/**
	 * @param $index  integer
	 * @param $length integer
	 * @return String_Class
	 */
	public function substr($index, $length = null)
	{
		return new String_Class(
			isset($length) ? substr($this->value, $index, $length) : substr($this->value, $index)
		);
	}

	//------------------------------------------------------------------------------------- substring
	/**
	 * @param $start integer
	 * @param $stop  integer
	 * @return String_Class
	 */
	public function substring($start, $stop = null)
	{
		return new String_Class(
			isset($stop) ? substr($this->value, $start, $stop - $start) : substr($this->value, $start)
		);
	}

	//--------------------------------------------------------------------------------------- textile
	/**
	 * Parse to textile
	 *
	 * @return String_Class
	 */
	public function textile()
	{
		$wiki = new Wiki();
		$text = $wiki->geshi($this->value, false);
		$text = $wiki->textile($text);
		$text = $wiki->geshiSolve($text);
		return new String_Class($text);
	}

	//------------------------------------------------------------------------------------- toInteger
	/**
	 * @return integer
	 */
	public function toInteger()
	{
		return intval($this->value);
	}

	//--------------------------------------------------------------------------------------- toWeeks
	/**
	 * @return integer
	 */
	public function toWeeks()
	{
		if ($this->value instanceof DateTime) {
			return $this->value->format('W');
		}
		return round($this->value / 60 / 60 / 24 / 7);
	}

	//--------------------------------------------------------------------------------------- twoLast
	/**
	 * The two last elements of a separated string
	 *
	 * @return String_Class
	 * @todo remove and replace .twoLast by .last(2) (needs debugging of Html_Template)
	 */
	public function twoLast() : String_Class
	{
		return $this->last(2);
	}

	//--------------------------------------------------------------------------------------- ucfirst
	/**
	 * @return String_Class
	 */
	public function ucfirst()
	{
		return new String_Class(ucfirst($this->value));
	}

	//--------------------------------------------------------------------------------------- ucwords
	/**
	 * @return String_Class
	 */
	public function ucwords()
	{
		return new String_Class(ucwords($this->value));
	}

	//----------------------------------------------------------------------------------------- upper
	/**
	 * @return String_Class
	 */
	public function upper()
	{
		return new String_Class(strtoupper($this->value));
	}

	//------------------------------------------------------------------------------------------- uri
	/**
	 * @return String_Class
	 */
	public function uri()
	{
		return new String_Class(strUri($this->value));
	}

	//------------------------------------------------------------------------------------ uriElement
	/**
	 * @return String_Class
	 */
	public function uriElement()
	{
		return new String_Class(strUriElement($this->value));
	}

}
