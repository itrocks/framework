<?php
namespace ITRocks\Framework\Layout\Generator;

use ITRocks\Framework\Layout\Structure\Field\Text;
use ITRocks\Framework\Layout\Structure\Group;
use ITRocks\Framework\Layout\Structure\Group\Iteration;
use ITRocks\Framework\Layout\Structure\Has_Structure;
use ITRocks\Framework\Layout\Structure\Page;

/**
 * Cut iterations that are to high for the available space on each page
 * Not working yet !
 *
 * @deprecated Replaced by a call to Cut_Iterations that cut even if not useful
 */
class Cut_Iterations_Late
{
	use Has_Output;
	use Has_Structure;

	//------------------------------------------------------------------------------------ MIN_HEIGHT
	/**
	 * Do not cut iterations if it is smaller than that : prefer moving it on next page
	 */
	const MIN_HEIGHT = 20;

	//---------------------------------------------------------------------------------- cutIteration
	/**
	 * @param $available_height float height available after the first iteration is printed
	 * @param $iteration        Iteration the iteration to cut
	 * @return Iteration|null the iteration that is added by cutting $iteration in two
	 */
	protected function cutIteration(float &$available_height, Iteration $iteration) : Iteration|null
	{
		$available_bottom         = $iteration->top + $available_height;
		$next_iteration           = clone $iteration;
		$next_iteration->elements = [];

		foreach ($iteration->elements as $element) {
			if (($element->bottom() <= $available_bottom) || !($element instanceof Text)) {
				continue;
			}
			[$p, $_p, $separator]       = $element->isFormatted() ? [P, _P, BR] : ['', '', LF];
			$element_height             = $element->height;
			$element_text               = $element->text;
			$next_element               = clone $element;
			$next_element->iteration    = $next_iteration;
			$next_iteration->elements[] = $next_element;
			$separators_count_high      = substr_count($element->text, $separator);
			$separators_count_low       = 1;
			while (abs($separators_count_low - $separators_count_high) > 1) {
				$separators_count = intdiv($separators_count_low + $separators_count_high, 2);
				$element->text    = lParse($element_text, $separator, $separators_count) . $_p;
				$element->calculateHeight($this->output);
				if ($element->bottom() < $available_bottom) {
					$separators_count_low = max($separators_count, $separators_count_low + 1);
				}
				else {
					$separators_count_high = min($separators_count, $separators_count_high - 1);
				}
			}
			$next_element->height = $element_height - $element->height;
			$next_element->text   = $p . substr(
				$element_text, strlen($element->text) - strlen($_p) + strlen($separator)
			);
		}

		if (!$next_iteration->elements) {
			return null;
		}

		$iteration->calculateHeight();
		$next_iteration->calculateHeight();
		$next_iteration->down($iteration->height);
		$available_height += $next_iteration->height;

		return $next_iteration;
	}

	//----------------------------------------------------------------------------------------- group
	/**
	 * Add pages depending on room needed by the group
	 *
	 * Here is an algorithm very near Count_Pages::group(). The only added thing is the call
	 * to cutIteration() and some refactoring to allow adding of iterations on the middle
	 *
	 * TODO LOW should be refactored so that things common with Count_Pages are maintained once
	 *
	 * @param $group Group
	 */
	protected function group(Group $group)
	{
		$cut_iterations   = [];
		$structure        = $this->structure;
		$page_number      = 1;
		$pages_count      = $structure->pages_count;
		$page             = $structure->page($page_number, $pages_count);
		$page_height      = $group->heightOnPage($page);
		$available_height = $page_height;

		$iterations       = array_values($group->iterations);
		$iteration_count  = count($iterations);
		$iteration_number = 0;
		while ($iteration_number < $iteration_count) {
			$iteration = $iterations[$iteration_number];
			if (
				($available_height < $iteration->height)
				&& ($page_number === $pages_count)
			) {
				$pages_count ++;
				$page              = $structure->page($page_number, $pages_count);
				$new_page_height   = $group->heightOnPage($page);
				$available_height += $new_page_height - $page_height;
				$page_height       = $new_page_height;
			}
			if (
				($available_height < $iteration->height)
				&& ($iteration->height > static::MIN_HEIGHT)
			) {
				$next_iteration = $this->cutIteration($available_height, $iteration);
				if ($next_iteration) {
					$iteration_count ++;
					$iterations = array_merge(
						array_slice($iterations, 0, $iteration_number + 1),
						[$next_iteration],
						array_slice($iterations, $iteration_number + 1)
					);
				}
			}
			if ($available_height < $iteration->height) {
				$page_number ++;
				$page             = $structure->page($page_number, $pages_count);
				$page_height      = $group->heightOnPage($page);
				$available_height = $page_height;
			}
			$available_height -= $iteration->height;
			$iteration_number       ++;
		}

		$group->iterations = $cut_iterations;
	}

	//----------------------------------------------------------------------------- minimumPagesCount
	/**
	 * Calculate the minimum pages count
	 *
	 * @example
	 * - one page or 'unique' page exists => 1
	 * - all other cases => 2
	 * @return integer
	 */
	protected function minimumPagesCount() : int
	{
		$pages = $this->structure->pages;
		if (isset($pages[Page::UNIQUE]) || (count($pages) === 1)) {
			return 1;
		}
		return 2;
	}

	//------------------------------------------------------------------------------------------- run
	public function run()
	{
		$this->structure->pages_count = $this->minimumPagesCount();
		foreach ($this->structure->pages as $page) {
			foreach ($page->groups as $group) {
				if ($group === reset($group->links)) {
					$this->group($group);
				}
			}
		}
	}

}
