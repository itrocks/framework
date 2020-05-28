<?php
namespace ITRocks\Framework\Layout\Generator;

use ITRocks\Framework\Layout\Structure\Element;
use ITRocks\Framework\Layout\Structure\Group;
use ITRocks\Framework\Layout\Structure\Has_Structure;

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
class Dispatch_Iterations
{
	use Has_Structure;

	//----------------------------------------------------------------------------------------- group
	/**
	 * @param $group Group
	 */
	protected function group(Group $group)
	{
		$previous_iterations_height = 0;
		foreach ($group->iterations as $iteration) {
			$iteration->top += $previous_iterations_height;
			$minimal_top     = reset($iteration->elements)->page->height;
			foreach ($iteration->elements as $element) {
				$element->top += $previous_iterations_height;
				$minimal_top   = min($minimal_top, $element->top);
			}
			// next line shift value
			$max_height                  = $this->maxHeight($iteration->elements) - $minimal_top;
			$iteration->height           = $max_height;
			$previous_iterations_height += $max_height + $group->iteration_spacing;
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
		$bottom = 0;
		$height = 0;
		$margin = 0;
		$top    = 0;
		foreach ($elements as $element) {
			if ($element->top > $top) {
				if ($bottom) {
					$margin = max($margin, $element->top - $bottom);
					$bottom = 0;
				}
				$top = $element->top;
			}
			$bottom = max($bottom, $element->top + $element->height);
			$height = max($height, $element->top + $element->height);
		}
		return $height + $margin;
	}

	//------------------------------------------------------------------------------------------- run
	public function run()
	{
		foreach ($this->structure->pages as $page) {
			foreach ($page->groups as $group) {
				$this->group($group);
			}
		}
	}

}
