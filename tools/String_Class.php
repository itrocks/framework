<?php
namespace SAF\Framework\Tools;

use SAF\Framework\Builder;
use SAF\Framework\Locale\Loc;

/**
 * A String class to get commonly used string features into an object
 */
class String_Class
{

	//------------------------------------------------------------------------------------ $translate
	/**
	 * @var boolean
	 */
	public $translate;

	//---------------------------------------------------------------------------------------- $value
	/**
	 * @var string
	 */
	public $value;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value     string
	 * @param $translate boolean
	 */
	public function __construct($value, $translate = false)
	{
		$this->value     = $value;
		$this->translate = $translate;
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
	 * @return String_Class the clean word.
	 * @example
	 * cleanWord('Albert, ') => return 'Albert'
	 * cleanWord(' list : ') => return 'list'
	 */
	function cleanWord()
	{
		return new String_Class(
			preg_replace('#[^a-zA-Zàáâãäåçèéêëìíîïðòóôõöùúûüýÿ\-\'\_\\\/]#', '', $this->value),
			$this->translate
		);
	}

	//--------------------------------------------------------------------------------------- display
	/**
	 * @return string
	 */
	public function display()
	{
		$display = str_replace('_', SP, $this->value);
		return $this->translate ? Loc::tr($display) : $display;
	}

	//---------------------------------------------------------------------------------------- escape
	/**
	 * @return String_Class
	 */
	public function escape()
	{
		return new String_Class(str_replace(BS, BS . BS, $this->value), $this->translate);
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
			if (strpos($this->value, $char) !== false) {
				return new String_Class(
					substr($this->value, 0, strpos($this->value, $char)), $this->translate
				);
			}
		}
		return new String_Class($this->value, $this->translate);
	}

	//---------------------------------------------------------------------------------- htmlEntities
	/**
	 * @return String_Class
	 */
	public function htmlEntities()
	{
		return new String_Class(htmlentities($this->value, ENT_QUOTES|ENT_HTML5), $this->translate);
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
	 * @return String_Class
	 */
	public function last($count = 1)
	{
		foreach ([':', DOT, '-', ','] as $char) {
			if (strrpos($this->value, $char) !== false) {
				return new String_Class(rLastParse($this->value, $char, $count, true), $this->translate);
			}
		}
		return new String_Class($this->value, $this->translate);
	}

	//----------------------------------------------------------------------------------------- lower
	/**
	 * @return String_Class
	 */
	public function lower()
	{
		return new String_Class(strtolower($this->value), $this->translate);
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
	 * @param $string    string
	 * @param $translate boolean
	 * @return self
	 */
	public static function of($string, $translate = false)
	{
		return new String_Class($string, $translate);
	}

	//------------------------------------------------------------------------------------------ path
	/**
	 * Changes a 'A\Class\Name' into 'A/Class/Name'
	 *
	 * @return String_Class
	 */
	public function path()
	{
		return new String_Class(
			str_replace(BS, SL, Builder::current()->sourceClassName($this->value)), $this->translate
		);
	}

	//----------------------------------------------------------------------------------------- short
	/**
	 * @return String_Class
	 */
	public function short()
	{
		return new String_Class(Namespaces::shortClassName($this->value), $this->translate);
	}

	//---------------------------------------------------------------------------------------- source
	/**
	 * Change a class name to a source class name
	 */
	public function source()
	{
		return new String_Class(Builder::current()->sourceClassName($this->value), $this->translate);
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
			isset($length) ? substr($this->value, $index, $length) : substr($this->value, $index),
			$this->translate
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
			isset($stop) ? substr($this->value, $start, $stop - $start) : substr($this->value, $start),
			$this->translate
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
		return new String_Class($text, $this->translate);
	}

	//--------------------------------------------------------------------------------------- twoLast
	/**
	 * The two last elements of a separated string
	 *
	 * @return String_Class
	 * @todo remove and replace .twoLast by .last(2) (needs debugging of Html_Template)
	 */
	public function twoLast()
	{
		return $this->last(2);
	}

	//--------------------------------------------------------------------------------------- ucfirst
	/**
	 * @return String_Class
	 */
	public function ucfirst()
	{
		return new String_Class(ucfirst($this->value), $this->translate);
	}

	//--------------------------------------------------------------------------------------- ucwords
	/**
	 * @return String_Class
	 */
	public function ucwords()
	{
		return new String_Class(ucwords($this->value), $this->translate);
	}

	//----------------------------------------------------------------------------------------- upper
	/**
	 * @return String_Class
	 */
	public function upper()
	{
		return new String_Class(strtoupper($this->value), $this->translate);
	}

	//------------------------------------------------------------------------------------------- uri
	/**
	 * @return String_Class
	 */
	public function uri()
	{
		return new String_Class(strUri($this->value), $this->translate);
	}

}
