<?php
namespace SAF\Framework;

abstract class List_Annotation
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * List string value is a values list, each one separated by a comma.
	 * Spaces before and after commas are ignored.
	 *
	 * @example "@values First value, Second one, etc"
	 * @param string $value
	 */
	public function __construct($value)
	{
		$values = array();
		$value = trim($value);
		$length = strlen($value);
		$in_quote = $length && (($value[0] === "'") || ($value[0] === '"'));
		$start = ($in_quote ? 1 : 0);
		$stop = null;
		$i = 0;
		while ($i < $length) {
			if ($value[$i] === "\\") {
				$i++;
			}
			elseif ($value[$i] === $in_quote) {
				$j = $i + 1;
				while (($j < $length) && ($value[$j] === " ")) $j ++;
				if ($value[$j] === ",") {
					$stop = $i;
					$in_quote = false;
					$i = $j;
				}
				else {
					trigger_error(
						"Badly formatted @" . strtolower(lParse(get_class($this), "_"))
						. " $value at position $i : "
						. (($in_quote === '"') ? "double " : "") . "quote must be followed by a comma",
						E_USER_ERROR
					);
				}
			}
			elseif (($value[$i] === ",") && !$in_quote) {
				if (!isset($stop)) $stop = $i;
				$values[] = substr($value, $start, $stop - $start);
				$i ++;
				while (($i < $length) && ($value[$i] === " ")) $i ++;
				$in_quote = ($i < $length) && (($value[$i] === "'") || ($value[$i] === '"'));
				$start = ($in_quote ? ($i + 1) : $i);
			}
			$i ++;
		}
		if ($in_quote) {
			trigger_error(
				"Badly formatted @" . strtolower(lParse(get_class($this), "_"))
				. " $value at position $i : "
				. (($in_quote === '"') ? "double " : "") . "quote not closed",
				E_WARNING
			);
		}
echo "values as " . print_r($values, true) . "<br>";
		parent::__construct($values);
	}

}
