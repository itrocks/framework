<?php
namespace SAF\Framework;

abstract class List_Annotation extends Annotation
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
		$in_quote = ($length && (($value[0] === "'") || ($value[0] === '"')))
			? $value[0] : false;
		$start = ($in_quote ? 1 : 0);
		$stop = null;
		$i = $start;
		while ($i < $length) {
			if (($value[$i] === "\\") && ($i < ($length - 1))) {
				$i++;
			}
			if ($value[$i] === $in_quote) {
				$j = $i + 1;
				while (($j < $length) && ($value[$j] === " ")) $j ++;
				$stop = $i;
				$in_quote = false;
				$i = $j;
			}
			if (($i == $length) || ($value[$i] === ",") && !$in_quote) {
				if (!isset($stop)) {
					$stop = $i;
				}
				$values[] = substr($value, $start, $stop - $start);
				$i ++;
				while (($i < $length) && ($value[$i] === " ")) $i ++;
				$in_quote = (($i < $length) && (($value[$i] === "'") || ($value[$i] === '"')))
					? $value[$i] : false;
				$start = ($in_quote ? ($i + 1) : $i);
				$stop = null;
			}
			$i++;
		}
		if (($i == $length) && ($values || ($i > $start))) {
			$values[] = substr($value, $start, $i - $start);
		}
		parent::__construct($values);
	}

}
