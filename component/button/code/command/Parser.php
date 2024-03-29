<?php
namespace ITRocks\Framework\Component\Button\Code\Command;

use ITRocks\Framework\Component\Button\Code\Command;

/**
 * Command parser
 */
class Parser
{

	//----------------------------------------------------------------------------------------- parse
	/**
	 * @param $source    string
	 * @param $condition boolean If true, consider the source is a condition
	 * @return ?Command null for nop
	 */
	public static function parse(string $source, bool $condition = false) : ?Command
	{
		if (str_contains($source, ':')) {
			[$property_name, $annotate] = explode(':', $source);
			return new Property_Annotation(trim($property_name), trim($annotate));
		}
		if (str_contains($source, '=')) {
			[$property_name, $value] = explode('=', $source);
			if ($condition) {
				return new Equals(trim($property_name), trim($value));
			}
			return new Assign(trim($property_name), trim($value));
		}
		elseif ($source && $condition) {
			return new Equals(trim($source), true);
		}
		return null;
	}

}
