<?php
namespace SAF\Framework\Tools;

/**
 * A String class to get commonly used string features into an object
 */
class String
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
	public function __construct($value)
	{
		$this->value = $value;
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return strval($this->value);
	}

	//------------------------------------------------------------------------------------- cleanWord
	/**
	 * Clean the word, this delete all character who don't have a place in a current word.
	 *
	 * @todo see if there is any conceptual difference with strSimplify. If not, replace it !
	 * @return String the clean word.
	 * @example
	 * cleanWord('Albert, ') => return 'Albert'
	 * cleanWord(' list : ') => return 'list'
	 */
	function cleanWord()
	{
		return new String(
			preg_replace('#[^a-zA-Zàáâãäåçèéêëìíîïðòóôõöùúûüýÿ\-\'\_\\\/]#', '', $this->value)
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
	 * @return String
	 */
	public function escape()
	{
		return new String(str_replace(BS, BS . BS, $this->value));
	}

	//----------------------------------------------------------------------------------------- first
	/**
	 * First element of a separated string
	 *
	 * @return String
	 */
	public function first()
	{
		foreach ([':', DOT, '-', ','] as $char) {
			if (strpos($this->value, $char) !== false) {
				return new String(substr($this->value, 0, strpos($this->value, $char)));
			}
		}
		return new String($this->value);
	}

	//---------------------------------------------------------------------------------- htmlEntities
	/**
	 * @return String
	 */
	public function htmlEntities()
	{
		return new String(htmlentities($this->value, ENT_QUOTES|ENT_HTML5));
	}

	//---------------------------------------------------------------------------------------- isWord
	/**
	 * Test is the string like a word
	 *
	 * @return integer 0 if it's not a word.
	 */
	function isWord()
	{
		return preg_match('#[a-zA-Zàáâãäåçèéêëìíîïðòóôõöùúûüýÿ]#', $this->value);
	}

	//------------------------------------------------------------------------------------------ last
	/**
	 * Last element of a separated string
	 *
	 * @param $count integer
	 * @return String
	 */
	public function last($count = 1)
	{
		foreach ([':', DOT, '-', ','] as $char) {
			if (strrpos($this->value, $char) !== false) {
				return new String(rLastParse($this->value, $char, $count, true));
			}
		}
		return new String($this->value);
	}

	//----------------------------------------------------------------------------------------- lower
	/**
	 * @return String
	 */
	public function lower()
	{
		return new String(strtolower($this->value));
	}

	//------------------------------------------------------------------------------------------ nbsp
	/**
	 * @return String
	 */
	public function nbsp()
	{
		return empty($this->value) ? new String('&nbsp;') : $this;
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
		return new String($string);
	}

	//------------------------------------------------------------------------------------------ path
	/**
	 * Changes a 'A\Class\Name' into 'A/Class/Name'
	 *
	 * @return String
	 */
	public function path()
	{
		return new String(str_replace(BS, SL, $this->value));
	}

	//----------------------------------------------------------------------------------------- short
	/**
	 * @return String
	 */
	public function short()
	{
		return new String(Namespaces::shortClassName($this->value));
	}

	//---------------------------------------------------------------------------------------- substr
	/**
	 * @param $index  integer
	 * @param $length integer
	 * @return String
	 */
	public function substr($index, $length = null)
	{
		return new String(
			isset($length) ? substr($this->value, $index, $length) : substr($this->value, $index)
		);
	}

	//------------------------------------------------------------------------------------- substring
	/**
	 * @param $start integer
	 * @param $stop  integer
	 * @return String
	 */
	public function substring($start, $stop = null)
	{
		return new String(
			isset($stop) ? substr($this->value, $start, $stop - $start) : substr($this->value, $start)
		);
	}

	//--------------------------------------------------------------------------------------- textile
	/**
	 * Parse to textile
	 *
	 * @return String
	 */
	public function textile()
	{
		$wiki = new Wiki();
		$text = $wiki->geshi($this->value, false);
		$text = $wiki->textile($text);
		$text = $wiki->geshiSolve($text);
		return new String($text);
	}

	//--------------------------------------------------------------------------------------- twoLast
	/**
	 * The two last elements of a separated string
	 *
	 * @todo remove and replace .twoLast by .last(2) (needs debugging of Html_Template)
	 * @return String
	 */
	public function twoLast()
	{
		return $this->last(2);
	}

	//--------------------------------------------------------------------------------------- ucfirst
	/**
	 * @return String
	 */
	public function ucfirst()
	{
		return new String(ucfirst($this->value));
	}

	//--------------------------------------------------------------------------------------- ucwords
	/**
	 * @return String
	 */
	public function ucwords()
	{
		return new String(ucwords($this->value));
	}

	//----------------------------------------------------------------------------------------- upper
	/**
	 * @return String
	 */
	public function upper()
	{
		return new String(strtoupper($this->value));
	}

	//------------------------------------------------------------------------------------------- uri
	/**
	 * @return String
	 */
	public function uri()
	{
		return new String(strUri($this->value));
	}

}
