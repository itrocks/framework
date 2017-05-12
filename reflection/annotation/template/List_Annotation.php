<?php
namespace ITRocks\Framework\Reflection\Annotation\Template;

use ITRocks\Framework\Reflection\Annotation;

/**
 * A list annotation can store multiple values, separated by commas
 *
 * @example annotation value 1, value 2,'value 3', 'value 4'
 */
class List_Annotation extends Annotation
{

	//---------------------------------------------------------------------------------------- $value
	/**
	 * Annotation value
	 *
	 * @var string[]
	 */
	public $value;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * List string value is a values list, each one separated by a comma.
	 * Spaces before and after commas are ignored.
	 *
	 * @example '@values First value, Second one, etc'
	 * @param $value string
	 */
	public function __construct($value)
	{
		$values = [];
		$value = trim($value);
		$length = strlen($value);
		$in_quote = ($length && (($value[0] === Q) || ($value[0] === DQ)))
			? $value[0] : false;
		$start = ($in_quote ? 1 : 0);
		$stop = null;
		$i = $start;
		while ($i < $length) {
			if (($value[$i] === BS) && ($i < ($length - 1))) {
				$i++;
			}
			if ($value[$i] === $in_quote) {
				$j = $i + 1;
				while (($j < $length) && ($value[$j] === SP)) $j ++;
				$stop = $i;
				$in_quote = false;
				$i = $j;
			}
			if (($i == $length) || ($value[$i] === ',') && !$in_quote) {
				if (!isset($stop)) {
					$stop = $i;
				}
				$values[] = substr($value, $start, $stop - $start);
				$i ++;
				if ($i == $length) {
					$start = $i;
					break;
				}
				while (($i < $length) && ($value[$i] === SP)) $i ++;
				$in_quote = (($i < $length) && (($value[$i] === Q) || ($value[$i] === DQ)))
					? $value[$i] : false;
				$start = ($in_quote ? ($i + 1) : $i);
				$stop = null;
			}
			$i++;
		}
		if (($i == $length) && ($values || ($i > $start))) {
			$values[] = substr($value, $start, $i - $start);
		}
		/** @noinspection PhpParamsInspection $values is now a string[] */
		parent::__construct($values);
	}

	//------------------------------------------------------------------------------------------- add
	/**
	 * Adds a value to the annotation list of values
	 *
	 * @param $value string
	 */
	public function add($value)
	{
		if (!$this->has($value)) {
			$this->value[] = $value;
		}
	}

	//------------------------------------------------------------------------------------------- has
	/**
	 * Returns true if the list annotation has value into its values
	 *
	 * @param $value string
	 * @return boolean
	 */
	public function has($value)
	{
		return in_array($value, $this->value);
	}

	//---------------------------------------------------------------------------------------- remove
	/**
	 * Remove a value and return true if the value was here and removed, false if the value
	 * already was not here
	 *
	 * @param $value string
	 * @return boolean
	 */
	public function remove($value)
	{
		$key = array_search($value, $this->value);
		if ($key !== false) {
			unset($this->value[$key]);
			return true;
		}
		return false;
	}

	//---------------------------------------------------------------------------------------- values
	/**
	 * @return string[]
	 */
	public function values()
	{
		return $this->value;
	}

}
