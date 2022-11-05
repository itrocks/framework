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
	protected function group(Group $group) : void
	{
		$previous_iterations_height = 0;
		foreach ($group->iterations as $iteration) {
			$iteration->top += $previous_iterations_height;
			foreach ($iteration->elements as $element) {
				$element->top += $previous_iterations_height;
			}
			$this->ignoreEmptyLines($iteration->elements);
			$previous_iterations_height += $iteration->calculateHeight() + $iteration->spacing();
		}
	}

	//------------------------------------------------------------------------------ ignoreEmptyLines
	/**
	 * Shift up elements positions in iteration in order to ignore empty lines
	 *
	 * @param $elements Element[]
	 */
	protected function ignoreEmptyLines(array $elements) : void
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
