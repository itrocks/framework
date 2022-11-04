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
	 * @var Date_Time|string
	 */
	public Date_Time|string $value;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value Date_Time|string
	 */
	public function __construct(Date_Time|string $value = '')
	{
		$this->value = $value;
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return $this->value;
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
	 * @return static the clean word.
	 * @todo see if there is any conceptual difference with strSimplify. If not, replace it !
	 */
	function cleanWord() : static
	{
		return new static(
			preg_replace('#[^a-zA-Zàáâãäåçèéêëìíîïðòóôõöùúûüýÿ_\-\'\\\/]#', '', $this->value)
		);
	}

	//--------------------------------------------------------------------------------------- display
	/**
	 * @return string
	 */
	public function display() : string
	{
		return str_replace('_', SP, $this->value);
	}

	//---------------------------------------------------------------------------------------- escape
	/**
	 * @return static
	 */
	public function escape() : static
	{
		return new static(str_replace(BS, BS . BS, $this->value));
	}

	//----------------------------------------------------------------------------------------- first
	/**
	 * First element of a separated string
	 *
	 * @return static
	 */
	public function first() : static
	{
		foreach ([':', DOT, '-', ','] as $char) {
			if (str_contains($this->value, $char)) {
				return new static(substr($this->value, 0, strpos($this->value, $char)));
			}
		}
		return new static($this->value);
	}

	//----------------------------------------------------------------------------------------- geshi
	/**
	 * Parse with geshi
	 *
	 * @param $programming_language string
	 * @return static
	 */
	public function geshi(string $programming_language = 'php') : static
	{
		$wiki = new Wiki();
		if ($programming_language === 'php' && !str_contains($this->value, '<?php')) {
			$programming_language = 'html';
		}
		$text = $wiki->geshi('@' . $programming_language . LF . $this->value . LF . '@');
		return new static($text);
	}

	//---------------------------------------------------------------------------------- htmlEntities
	/**
	 * @return static
	 */
	public function htmlEntities() : static
	{
		return new static(htmlentities($this->value, ENT_QUOTES|ENT_HTML5));
	}

	//------------------------------------------------------------------------------ htmlSpecialChars
	/**
	 * @return static
	 */
	public function htmlSpecialChars() : static
	{
		return new static(htmlspecialchars($this->value));
	}

	//----------------------------------------------------------------------------------------- idTag
	/**
	 * Format the value so that it can be used into an id="" HTML tag
	 *
	 * @return string
	 */
	public function idTag() : string
	{
		return str_replace([DOT, SL], '-', $this->uri());
	}

	//---------------------------------------------------------------------------------------- isWord
	/**
	 * Test is the string like a word
	 *
	 * @return bool false if it's not a word.
	 */
	public function isWord() : bool
	{
		return preg_match('#[a-zA-Zàáâãäåçèéêëìíîïðòóôõöùúûüýÿ]#', $this->value);
	}

	//------------------------------------------------------------------------------------------ last
	/**
	 * Last element of a separated string
	 *
	 * @param $count integer
	 * @return static
	 */
	public function last(int $count = 1) : static
	{
		foreach ([':', DOT, '-', ','] as $char) {
			if (strrpos($this->value, $char) !== false) {
				return new static(rLastParse($this->value, $char, $count, true));
			}
		}
		return new static($this->value);
	}

	//------------------------------------------------------------------------------------------ left
	/**
	 * @param $length integer
	 * @return string
	 */
	public function left(int $length) : string
	{
		return new static(substr($this->value, 0, $length));
	}

	//---------------------------------------------------------------------------------------- length
	/**
	 * @return integer
	 */
	public function length() : int
	{
		return strlen($this->value);
	}

	//----------------------------------------------------------------------------------------- lower
	/**
	 * @return static
	 */
	public function lower() : static
	{
		return new static(strtolower($this->value));
	}

	//------------------------------------------------------------------------------------------ nbsp
	/**
	 * If string is empty, return an unbreakable HTML space. Useful to avoid empty cells.
	 *
	 * @noinspection PhpUnused list_/body.html
	 * @return static
	 */
	public function emptyNbsp() : static
	{
		return empty($this->value) ? new static('&nbsp;') : $this;
	}

	//-------------------------------------------------------------------------------------------- of
	/**
	 * Constructs a new String
	 *
	 * @param $string string
	 * @return static
	 */
	public static function of(string $string) : static
	{
		return new static($string);
	}

	//------------------------------------------------------------------------------------------ path
	/**
	 * Changes a 'A\Class\Name' into 'A/Class/Name'
	 *
	 * @return static
	 */
	public function path() : static
	{
		return new static(str_replace(BS, SL, Builder::current()->sourceClassName($this->value)));
	}

	//----------------------------------------------------------------------------------------- right
	/**
	 * @param $length integer
	 * @return static
	 */
	public function right(int $length) : static
	{
		return new static(substr($this->value, -$length));
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
	 * @return static
	 */
	public function short() : static
	{
		return new static(Namespaces::shortClassName($this->value));
	}

	//------------------------------------------------------------------------------------------ sign
	/**
	 * @return string
	 */
	public function sign() : string
	{
		return str_starts_with($this->value, '-') ? '-' : '+';
	}

	//---------------------------------------------------------------------------------------- source
	/**
	 * Change a class name to a source class name
	 */
	public function source()
	{
		return new static(Builder::current()->sourceClassName($this->value));
	}

	//---------------------------------------------------------------------------------------- substr
	/**
	 * @param $index  integer
	 * @param $length integer|null
	 * @return static
	 */
	public function substr(int $index, int $length = null) : static
	{
		return new static(substr($this->value, $index, $length));
	}

	//------------------------------------------------------------------------------------- substring
	/**
	 * @param $start integer
	 * @param $stop  integer|null
	 * @return static
	 */
	public function substring(int $start, int $stop = null) : static
	{
		return new static(substr($this->value, $start, isset($stop) ? ($stop - $start) : null));
	}

	//--------------------------------------------------------------------------------------- textile
	/**
	 * Parse to textile
	 *
	 * @return static
	 */
	public function textile() : static
	{
		$wiki = new Wiki();
		$text = $wiki->geshi($this->value, false);
		$text = $wiki->textile($text);
		$text = $wiki->geshiSolve($text);
		return new static($text);
	}

	//------------------------------------------------------------------------------------- toInteger
	/**
	 * @return integer
	 */
	public function toInteger() : int
	{
		return intval($this->value);
	}

	//--------------------------------------------------------------------------------------- toWeeks
	/**
	 * @return integer
	 */
	public function toWeeks() : int
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
	 * @return static
	 * @todo remove and replace .twoLast by .last(2) (needs debugging of Html_Template)
	 */
	public function twoLast() : static
	{
		return $this->last(2);
	}

	//--------------------------------------------------------------------------------------- ucfirst
	/**
	 * @return static
	 */
	public function ucfirst() : static
	{
		return new static(ucfirst($this->value));
	}

	//--------------------------------------------------------------------------------------- ucwords
	/**
	 * @return static
	 */
	public function ucwords() : static
	{
		return new static(ucwords($this->value));
	}

	//----------------------------------------------------------------------------------------- upper
	/**
	 * @return static
	 */
	public function upper() : static
	{
		return new static(strtoupper($this->value));
	}

	//------------------------------------------------------------------------------------------- uri
	/**
	 * @return static
	 */
	public function uri() : static
	{
		return new static(strUri($this->value));
	}

	//------------------------------------------------------------------------------------ uriElement
	/**
	 * @return static
	 */
	public function uriElement() : static
	{
		return new static(strUriElement($this->value));
	}

}
