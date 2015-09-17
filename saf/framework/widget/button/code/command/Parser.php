<?php
namespace SAF\Framework\Widget\Button\Code\Command;

use SAF\Framework\Widget\Button\Code\Command;

/**
 * Command parser
 */
class Parser
{

	//----------------------------------------------------------------------------------------- parse
	/**
	 * @param $source string
	 *
	 * @return Command|null null for nop
	 */
	public static function parse($source)
	{
		if (strpos($source, '=')) {
			list($property_name, $value) = explode('=', $source);
			return new Assign(trim($property_name), trim($value));
		}
		return null;
	}

}
