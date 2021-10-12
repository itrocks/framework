<?php
namespace ITRocks\Framework\Layout\Generator;

use ITRocks\Framework\Layout\Structure\Field\Text;
use ITRocks\Framework\Layout\Structure\Group;
use ITRocks\Framework\Layout\Structure\Group\Iteration;
use ITRocks\Framework\Layout\Structure\Has_Structure;

/**
 * Cut iterations by their multi-line element content carriage returns
 *
 * Limitations :
 * - will work with only one multi-line element
 * - only the first line of the multi-line element stays into the original iteration
 * - all other elements stay on the first iteration
 * - next iterations contain only the multi-line element next lines
 */
class Cut_Iterations
{
	use Has_Structure;

	//----------------------------------------------------------------------------------------- group
	/**
	 * @param $group Group
	 */
	protected function group(Group $group)
	{
		$iterations       = array_values($group->iterations);
		$iteration_number = 0;
		$iterations_count = count($iterations);
		while ($iteration_number < $iterations_count) {
			$iteration       = $iterations[$iteration_number];
			$more_iterations = $this->iteration($iteration);
			$iteration_number ++;
			if ($more_iterations) {
				$iterations_count += count($more_iterations);
				$iterations        = array_merge(
					array_slice($iterations, 0, $iteration_number),
					$more_iterations,
					array_slice($iterations, $iteration_number)
				);
			}
		}
		$group->iterations = $iterations;
	}

	//------------------------------------------------------------------------------------- iteration
	/**
	 * @param $iteration Iteration
	 * return Iteration[] added iterations
	 */
	protected function iteration(Iteration $iteration) : array
	{
		$iterations = [];
		foreach ($iteration->elements as $element) {
			if (!(
				($element instanceof Text)
				&& (str_contains($element->text, BR) || str_contains($element->text, LF))
			)) {
				continue;
			}
			$has_line_empty     = false;
			$last_line_empty    = true;
			$next_element       = null;
			$next_iteration     = null;
			[$text, $separator] = $element->isFormatted()
				? [substr($element->text, 3, -4), BR]
				: [$element->text, LF];
			foreach (array_slice(explode($separator, $text), 1) as $element_text) {
				$last_line_empty = !strlen(trim($element_text));
				if ($last_line_empty) {
					$has_line_empty = true;
				}
				$next_iteration             = clone $iteration;
				$next_iteration->elements   = [];
				$next_element               = clone $element;
				$next_element->iteration    = $next_iteration;
				$next_iteration->spacing    = false;
				$next_element->text         = P . $element_text . _P;
				$next_iteration->elements[] = $next_element;
				$next_iteration->up($next_element->top - $next_iteration->top, true);
				$iterations[] = $next_iteration;
			}
			$iteration->spacing      = false;
			$next_iteration->spacing = true;
			if ($has_line_empty && !$last_line_empty && $next_element) {
				$next_element->text = substr($next_element->text, 0, -4) . BR . _P;
			}
			$element->text = lParse($element->text, $separator) . _P;
		}
		return $iterations;
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
