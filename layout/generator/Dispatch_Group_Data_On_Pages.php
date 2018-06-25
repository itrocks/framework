<?php
namespace ITRocks\Framework\Layout\Generator;

use ITRocks\Framework\Layout\Structure\Element;
use ITRocks\Framework\Layout\Structure\Field;
use ITRocks\Framework\Layout\Structure\Group;
use ITRocks\Framework\Layout\Structure\Group\Iteration;

/**
 * Dispatch group data on pages : all data that is lower than group bottom must go next page
 *
 * This also decides if we use unique page / first + last pages / first + middle + last pages
 */
class Dispatch_Group_Data_On_Pages
{
	use Has_Structure;

	//----------------------------------------------------------------------------------------- group
	/**
	 * @param $group Group
	 */
	protected function group(Group $group)
	{
		$group_bottom = $group->bottom();
		$move_to_page = null;
		foreach ($group->elements as $iteration) {
			/** @var $iteration Iteration At this stage all group elements are Iteration, nothing else */
			$move_to_next_page = false;
			/** @var $element Element[] */
			foreach ($iteration->elements as $element) {
				if (($element instanceof Field) && ($element->bottom() > $group_bottom)) {
					$move_to_next_page = true;
					break;
				}
			}
			if ($move_to_next_page) {

			}
			if (isset($move_to_page)) {

			}
		}
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
