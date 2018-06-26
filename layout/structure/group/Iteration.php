<?php
namespace ITRocks\Framework\Layout\Structure\Group;

use ITRocks\Framework\Layout\Structure\Element;

/**
 * An iteration is a "line" (or column) of data into a group
 */
class Iteration extends Element
{

	//----------------------------------------------------------------------------------- DUMP_SYMBOL
	const DUMP_SYMBOL = 'x';

	//------------------------------------------------------------------------------------- $elements
	/**
	 * @mandatory
	 * @var Element[]
	 */
	public $elements;

	//--------------------------------------------------------------------------------------- $number
	/**
	 * Iteration number : 0..n
	 *
	 * @var integer
	 */
	public $number;

	//------------------------------------------------------------------------------------------ dump
	/**
	 * @param $level integer
	 * @return string
	 */
	public function dump($level = 0)
	{
		$dump = parent::dump($level) . SP . '(' . $this->number . ')' . LF;
		foreach ($this->elements as $element) {
			$dump .= $element->dump($level + 1) . LF;
		}
		return $dump;
	}

}
