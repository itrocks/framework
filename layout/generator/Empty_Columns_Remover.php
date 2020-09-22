<?php
namespace ITRocks\Framework\Layout\Generator;

use ITRocks\Framework\Layout\Generator;
use ITRocks\Framework\Layout\Structure\Element;
use ITRocks\Framework\Layout\Structure\Field;
use ITRocks\Framework\Layout\Structure\Field\Property;
use ITRocks\Framework\Layout\Structure\Field\Text;
use ITRocks\Framework\Layout\Structure\Group;
use ITRocks\Framework\Layout\Structure\Has_Structure;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\Tools\Call_Stack;

/**
 * This class allow to remove empty columns from a structure
 *
 * All properties are public to easily allow extensions (eg Identical_Columns_Remover)
 *
 * @feature Automatically remove empty columns from prints
 */
class Empty_Columns_Remover implements Registerable
{
	use Has_Structure;

	//------------------------------------------------------------------------------------------ $set
	/**
	 * @var boolean[]
	 */
	public $set;

	//--------------------------------------------------------------------------------------- $shifts
	/**
	 * @var float[] float[$column: integer]
	 */
	public $shifts;

	//---------------------------------------------------------------------------------------- $unset
	/**
	 * @var Property[]
	 */
	public $unset;

	//--------------------------------------------------------------------------------------- $widths
	/**
	 * @var float[] float[$column: integer
	 */
	public $widths;

	//----------------------------------------------------------------------------- applyShiftsWidths
	/**
	 * @input $shifts, $widths
	 * @param $elements Element[]
	 */
	protected function applyShiftsWidths(array $elements)
	{
		foreach ($this->shifts as $column => $shift) {
			if (isset($elements[$column])) {
				$elements[$column]->left += $shift;
			}
		}
		foreach ($this->widths as $column => $width) {
			if (isset($elements[$column])) {
				$elements[$column]->width += $width;
			}
		}
	}

	//----------------------------------------------------------------------- beforeAutomaticLineFeed
	/**
	 * @call run
	 * @output $structure
	 */
	public function beforeAutomaticLineFeed()
	{
		$generator = (new Call_Stack)->getObject(Generator::class);
		$generator->sortPageElements(false);
		$this->structure = $generator->structure;
		$this->run();
	}

	//---------------------------------------------------------------------------------- emptyColumns
	/**
	 * @output $set, $unset
	 * @param $group      Group
	 * @param $properties Element[]|Property[]
	 */
	protected function emptyColumns(Group $group, array $properties)
	{
		$properties_count = count($properties);
		$this->set        = [];
		foreach ($group->iterations as $iteration) {
			usort($iteration->elements, function(Field $element1, Field $element2) {
				return (abs($element1->top - $element2->top) >= Generator::$precision)
					? cmp($element1->top, $element2->top)
					: cmp($element1->hotX(), $element2->hotX());
			});
			for ($column = 0; $column < $properties_count; $column ++) {
				if (isset($this->set[$column])) {
					continue;
				}
				$element = $iteration->elements[$column];
				if (!($element instanceof Text) || !strlen($element->text)) {
					continue;
				}
				$this->set[$column] = true;
				if (count($this->set) === $properties_count) {
					$this->unset = [];
					return;
				}
			}
		}
		$this->unset = array_diff_key($properties, $this->set);
	}

	//--------------------------------------------------------------------------------------- headers
	/**
	 * - List header elements
	 * - Remove unset headers from elements
	 * Need elements to be sorted by y then x
	 *
	 * @output $headers
	 * @param $group      Group
	 * @param $properties Element[]|Property[]
	 * @param $alter      boolean if true, headers are removed. Not if false.
	 * @return Text[][]
	 */
	public function headers(Group $group, array $properties, $alter = false)
	{
		$headers = [];
		foreach ($group->links ?: [$group] as $group) {
			$elements = $group->page->elements;
			$element  = end($elements);
			while ($element && ($element->top > $group->top)) {
				$element = prev($elements);
			}
			if (!$element) {
				continue;
			}
			$property = end($properties);
			while ($element->left > ($property->left + ($property->width / 2))) {
				$element = prev($elements);
			}
			$element_top = $element->top;
			do {
				$column = key($properties);
				if (isset($this->set[$column])) {
					$headers[$group->page->number][$column] = $element;
				}
				elseif ($alter) {
					unset($elements[key($elements)]);
				}
				prev($properties);
				$element = prev($elements);
			}
			while ($element && (abs($element_top - $element->top) < Generator::$precision));
			if ($alter) {
				$group->page->elements = $elements;
			}
		}
		return $headers;
	}

	//------------------------------------------------------------------------------------ properties
	/**
	 * @param $group Group
	 * @return Element[]|Property[]
	 */
	protected function properties(Group $group)
	{
		$properties = [];
		$top        = reset($group->properties)->top;
		foreach ($group->properties as $column => $property) {
			if (abs($property->top - $top) < Generator::$precision) {
				$properties[$column] = $property;
			}
		}
		foreach ($group->elements as $column => $element) {
			if (abs($element->top - $top) < Generator::$precision) {
				$properties[]   = $element;
				$added_elements = true;
			}
		}
		if (isset($added_elements)) {
			usort($properties, function(Field $property1, Field $property2) {
				return cmp($property1->hotX(), $property2->hotX());
			});
		}
		return $properties;
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Register
	 */
	public function register(Register $register)
	{
		$register->aop->beforeMethod(
			[Automatic_Line_Feed::class, 'run'], [$this, 'beforeAutomaticLineFeed']
		);
	}

	//-------------------------------------------------------------------------------- removeElements
	/**
	 * @param $elements Element[]
	 */
	protected function removeElements(array &$elements)
	{
		foreach (array_keys($this->unset) as $key) {
			unset($elements[$key]);
		}
	}

	//------------------------------------------------------------------------------------------- run
	/**
	 * @call runGroup
	 */
	public function run()
	{
		foreach ($this->structure->pages as $page) {
			foreach ($page->groups as $group) {
				$this->runGroup($group);
			}
		}
	}

	//-------------------------------------------------------------------------------------- runGroup
	/**
	 * @param $group Group
	 */
	protected function runGroup(Group $group)
	{
		if (!$group->iterations) {
			return;
		}
		$properties = $this->properties($group);
		$this->emptyColumns($group, $properties);
		if (!$this->unset) {
			return;
		}
		$page_headers = $this->headers($group, $properties, true);
		$this->shiftsWidths($properties);
		foreach ($page_headers as $headers) {
			$this->applyShiftsWidths($headers);
		}
		foreach ($group->iterations as $iteration) {
			$this->applyShiftsWidths($iteration->elements);
			$this->removeElements($iteration->elements);
		}
	}

	//---------------------------------------------------------------------------------- shiftsWidths
	/**
	 * Calculate column shifts / widths increment
	 *
	 * @input $unset
	 * @output $shifts, $widths
	 * @param $properties Element[]|Property[]
	 */
	protected function shiftsWidths(array $properties)
	{
		$property = end($this->unset);
		$column   = key($this->unset);
		$right    = ($column === (count($properties) - 1))
			? $property->right()
			: $properties[$column + 1]->left;
		$shift        = 0;
		$this->shifts = [];
		$this->widths = [];
		do {
			$shift        += ($right - $property->left);
			$right        = $property->left;
			$column       --;
			$property     = prev($this->unset);
			$unset_column = key($this->unset) ?: -1;
			while ($column > $unset_column) {
				$set_property = $properties[$column];
				$right        = $set_property->left;
				if ($set_property->text_align === Property::LEFT) {
					$this->widths[$column] = $shift;
					$shift                 = 0;
				}
				else {
					$this->shifts[$column] = $shift;
				}
				$column --;
			}
		}
		while ($property);
	}

}
