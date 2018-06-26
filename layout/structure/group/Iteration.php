<?php
namespace ITRocks\Framework\Layout\Structure\Group;

use ITRocks\Framework\Layout\Structure\Element;
use ITRocks\Framework\Layout\Structure\Group;
use ITRocks\Framework\Layout\Structure\Page;

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

	//------------------------------------------------------------------------------ cloneWithContext
	/**
	 * @param $page      Page
	 * @param $group     Group|null
	 * @param $iteration Iteration|null
	 * @return static
	 */
	public function cloneWithContext(Page $page, Group $group = null, Iteration $iteration = null)
	{
		/** @var $iteration Iteration PhpStorm bug */
		$iteration = parent::cloneWithContext($page, $group, $iteration);

		$elements = [];
		foreach ($this->elements as $element) {
			$elements[] = $element->cloneWithContext($page, $group, $iteration);
		}
		$this->elements = $elements;

		return $iteration;
	}

	//------------------------------------------------------------------------------------------ dump
	/**
	 * @param $level integer
	 * @return string
	 */
	public function dump($level = 0)
	{
		$dump = parent::dump($level) . SP . '(' . $this->number . ')' . LF;
		foreach ($this->elements as $element) {
			/** @var $element Element */
			$dump .= $element->dump($level + 1) . LF;
		}
		return $dump;
	}

}
