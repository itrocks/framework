<?php
namespace ITRocks\Framework\Layout\Generator;

use ITRocks\Framework\Layout\Structure\Element;
use ITRocks\Framework\Layout\Structure\Group;
use ITRocks\Framework\Layout\Structure\Group\Iteration;

/**
 * Dispatch group data
 *
 * On input : all data in groups are superimposed when multiple.
 * On input : the data in groups are dispatched to avoid data superimposing.
 *
 * The dispatch is done on the page where all data is : the data is not dispatched between pages.
 *
 * @see Dispatch_Group_Data_On_Pages for this next step
 * @todo only vertical Group is managed. Do this for horizontal when will be useful
 */
class Dispatch_Group_Data
{
	use Has_Structure;

	//----------------------------------------------------------------------------------------- group
	/**
	 * @param $group Group
	 */
	protected function group(Group $group)
	{
		$previous_iterations_height = 0;
		foreach ($group->elements as $iteration) {
			/** @var $iteration Iteration At this stage all group elements are Iteration, nothing else */
			if ($previous_iterations_height) {
				foreach ($iteration->elements as $element) {
					$element->top += $previous_iterations_height;
				}
			}
			// next line shift value
			$previous_iterations_height += $this->maxHeight($iteration->elements);
		}
	}

	//------------------------------------------------------------------------------------- maxHeight
	/**
	 * Calculate the maximum height occupied by the group iteration (eg line)
	 *
	 * This takes care of the top position of the highest element : this this the total height of the
	 * line
	 *
	 * @param $elements Element[]
	 * @return float
	 */
	protected function maxHeight(array $elements)
	{
		// minimal top (top position of the highest element in the page)
		$minimal_top = reset($elements)->page->height;
		foreach ($elements as $element) {
			$minimal_top = min($minimal_top, $element->top);
		}
		// maximal height
		$height = 0;
		foreach ($elements as $element) {
			$height = max($height, $element->height + ($element->top - $minimal_top));
		}
		return $height;
	}

	//------------------------------------------------------------------------------------------- run
	public function run()
	{
		foreach ($this->structure->pages as $page) {
			foreach ($page->elements as $element) {
				if ($element instanceof Group) {
					$this->group($element);
				}
			}
		}
	}

}
