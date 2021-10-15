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
		$iteration->sortElementsByY();
		$element = reset($iteration->elements);
		while ($element) {
			if (!(
				($element instanceof Text) && $element->isFormatted() && str_contains($element->text, BR)
			)) {
				$element = next($iteration->elements);
				continue;
			}
			$has_line_empty     = false;
			$last_line_empty    = true;
			$next_element       = null;
			$next_iteration     = null;
			[$text, $separator, $begin, $end] = $element->isFormatted()
				? [substr($element->text, 3, -4), BR, P, _P]
				: [$element->text, LF, '', ''];
			foreach (array_slice(explode($separator, $text), 1) as $element_text) {
				$last_line_empty = !strlen(trim($element_text));
				if ($last_line_empty) {
					$has_line_empty = true;
				}
				$next_element               = clone $element;
				$next_iteration             = clone $iteration;
				$next_element->iteration    = $next_iteration;
				$next_iteration->elements   = [$next_element];
				$next_iteration->spacing    = false;
				$next_element->text         = $begin . $element_text . $end;
				$next_iteration->up($next_element->top - $next_iteration->top, true);
				$iterations[] = $next_iteration;
			}
			$iteration->spacing      = false;
			$next_iteration->spacing = true;
			if ($has_line_empty && !$last_line_empty && $next_element) {
				$next_element->text = substr($next_element->text, 0, -4) . $separator . $end;
			}
			$element->text = lParse($element->text, $separator) . $end;
			// move all elements below the original element into the original iteration as a new iteration
			foreach ($iteration->elements as $key => $other_element) {
				if ($other_element->top > $element->top) {
					$next_iteration             = clone $iteration;
					$next_iteration->elements   = [$other_element];
					$other_element->iteration   = $next_iteration;
					$next_iteration->up($other_element->top - $next_iteration->top, true);
					unset($iteration->elements[$key]);
					$iterations[] = $next_iteration;
				}
			}
			$element = next($iteration->elements);
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
