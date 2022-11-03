<?php
namespace ITRocks\Framework\Reflection\Annotation\Template;

use ITRocks\Framework\Reflection\Annotation;

/**
 * A list annotation can store multiple values, separated by commas
 *
 * @example annotation value 1, value 2,'value 3', 'value 4'
 * @override value @var ?string[]
 * @property ?string[] value
 */
class List_Annotation extends Annotation
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * List string value is a values list, each one separated by a comma.
	 * Spaces before and after commas are ignored.
	 *
	 * @example '@values First value, Second one, etc'
	 * @param $value ?string
	 */
	public function __construct(?string $value)
	{
		parent::__construct($value);
		$values   = [];
		$value    = trim(strval($value));
		$length   = strlen($value);
		$in_quote = ($length && (($value[0] === Q) || ($value[0] === DQ))) ? $value[0] : false;
		$start    = ($in_quote ? 1 : 0);
		$stop     = null;
		$position = $start;
		while ($position < $length) {
			if (($value[$position] === BS) && ($position < ($length - 1))) {
				$position ++;
			}
			if ($value[$position] === $in_quote) {
				$next_position = $position + 1;
				while (($next_position < $length) && ($value[$next_position] === SP)) {
					$next_position ++;
				}
				$stop     = $position;
				$in_quote = false;
				$position = $next_position;
			}
			if (($position === $length) || ($value[$position] === ',') && !$in_quote) {
				if (!isset($stop)) {
					$stop = $position;
				}
				$values[] = trim(substr($value, $start, $stop - $start));
				$position ++;
				if ($position === $length) {
					$start = $position;
					break;
				}
				while (($position < $length) && ($value[$position] === SP)) {
					$position ++;
				}
				$in_quote = (
					($position < $length)
					&& (($value[$position] === Q) || ($value[$position] === DQ))
				)
					? $value[$position]
					: false;
				$start = ($in_quote ? ($position + 1) : $position);
				$stop  = null;
			}
			$position ++;
		}
		if (($position === $length) && ($values || ($position > $start))) {
			$values[] = substr($value, $start, $position - $start);
		}
		$this->value = $values;
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return '[' . join(',', $this->value) . ']';
	}

	//------------------------------------------------------------------------------------------- add
	/**
	 * Adds a value to the annotation list of values
	 *
	 * @param $value string
	 */
	public function add(string $value)
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
	public function has(string $value) : bool
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
	public function remove(string $value) : bool
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
	public function values() : array
	{
		return $this->value;
	}

}
