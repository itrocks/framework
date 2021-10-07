<?php
namespace ITRocks\Framework\Layout\Generator;

use ITRocks\Framework\Layout\Output;
use ITRocks\Framework\Layout\Structure\Element;
use ITRocks\Framework\Layout\Structure\Group;
use ITRocks\Framework\Layout\Structure\Group\Iteration;
use ITRocks\Framework\Layout\Structure\Has_Structure;

/**
 * Common code for generator steps that shift top positions and enlarge widths
 */
abstract class Shift_Top
{
	use Has_Structure;

	//--------------------------------------------------------------------------------------- element
	/**
	 * @param $element Element
	 * @return float The height increase
	 */
	abstract protected function element(Element $element) : float;

	//----------------------------------------------------------------------------------------- group
	/**
	 * @param $group Group
	 */
	protected function group(Group $group)
	{
		foreach ($group->elements as $element) {
			$this->element($element);
		}
		foreach ($group->groups as $sub_group) {
			$this->group($sub_group);
		}
		foreach ($group->iterations as $iteration) {
			$this->iteration($iteration);
		}
	}

	//------------------------------------------------------------------------------------- iteration
	/**
	 * @param $iteration Iteration
	 */
	protected function iteration(Iteration $iteration)
	{
		$shift     = 0;
		$shift_top = 0;
		$top       = -1;
		foreach ($iteration->elements as $element) {
			if ($element->top > $top) {
				$shift_top += $shift;
				$shift      = 0;
				$top        = $element->top;
			}
			$element->top += $shift_top;
			$shift         = max($shift, $this->element($element));
		}
	}

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $output Output
	 */
	public function run(Output $output)
	{
		$this->output = $output;
		foreach ($this->structure->pages as $page) {
			foreach ($page->elements as $element) {
				$this->element($element);
			}
			foreach ($page->groups as $group) {
				$this->group($group);
			}
		}
	}

}
