<?php
namespace ITRocks\Framework\Layout\Generator;

use ITRocks\Framework\Layout\Structure\Element;
use ITRocks\Framework\Layout\Structure\Field\Text;
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
			foreach ($iteration->elements as $element) {
				$element->top += $previous_iterations_height;
			}
			$this->ignoreEmptyLines($iteration->elements);
			$max_height                  = $this->maxHeight($iteration->elements);
			$iteration->height           = $max_height;
			$previous_iterations_height += $max_height + $group->iteration_spacing;
		}
	}

	//------------------------------------------------------------------------------ ignoreEmptyLines
	/**
	 * Shift up elements positions in iteration in order to ignore empty lines
	 *
	 * @param $elements Element[]
	 */
	protected function ignoreEmptyLines(array $elements)
	{
		$line_has_value = false;
		$line_top       = reset($elements)->top;
		$shift_up       = 0;
		foreach ($elements as $element) {
			if ($element->top > $line_top) {
				if (!$line_has_value) {
					$shift_up += $element->top - $line_top;
				}
				$line_has_value = false;
				$line_top       = $element->top;
			}
			if (!($element instanceof Text) || strlen($element->text)) {
				$line_has_value = true;
			}
			if ($shift_up) {
				$element->top -= $shift_up;
			}
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
	protected function maxHeight(array $elements) : float
	{
		$iteration_bottom = 0;
		$iteration_margin = 0;
		$iteration_top    = reset($elements)->top;
		$line_bottom      = 0;
		$line_top         = 0;
		foreach ($elements as $element) {
			if ($element->top > $line_top) {
				if ($line_bottom) {
					$iteration_margin = max($iteration_margin, $element->top - $line_bottom);
					$line_bottom      = 0;
				}
				$line_top = $element->top;
			}
			$line_bottom      = max($line_bottom, $element->top + $element->height);
			$iteration_bottom = max($iteration_bottom, $line_bottom);
		}
		return $iteration_bottom + $iteration_margin - $iteration_top;
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
