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

	//------------------------------------------------------------------------------------- $elements
	/**
	 * @var Element[]
	 */
	public $elements;

	//---------------------------------------------------------------------------------------- $group
	/**
	 * @var Group
	 */
	public $group;

	//-------------------------------------------------------------------------------------- $headers
	/**
	 * @var Text[]
	 */
	public $headers;

	//----------------------------------------------------------------------------------- $properties
	/**
	 * @var Element[]|Property[]
	 */
	public $properties;

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
	 * @input  $group->iterations, $properties
	 * @output $set, $unset
	 */
	protected function emptyColumns()
	{
		$properties_count = count($this->properties);
		$this->set        = [];
		foreach ($this->group->iterations as $iteration) {
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
		$this->unset = array_diff_key($this->properties, $this->set);
	}

	//--------------------------------------------------------------------------------------- headers
	/**
	 * - List header elements
	 * - Remove unset headers from elements
	 * Need elements to be sorted by y then x
	 *
	 * @input $elements, $group->top, $properties
	 * @output $headers, $elements
	 */
	protected function headers()
	{
		$element = end($this->elements);
		while ($element->top > $this->group->top) {
			$element = prev($this->elements);
		}
		$property = end($this->properties);
		while ($element->left > ($property->left + ($property->width / 2))) {
			$element = prev($this->elements);
		}
		$element_top   = $element->top;
		$this->headers = [];
		do {
			$column = key($this->properties);
			if (isset($this->set[$column])) {
				$this->headers[$column] = $element;
			}
			else {
				unset($this->elements[key($this->elements)]);
			}
			prev($this->properties);
			$element = prev($this->elements);
		}
		while (abs($element_top - $element->top) < Generator::$precision);
	}

	//------------------------------------------------------------------------------------ properties
	/**
	 * @input $group->properties
	 * @output $properties
	 */
	protected function properties()
	{
		$this->properties = [];
		$top              = reset($this->group->properties)->top;
		foreach ($this->group->properties as $column => $property) {
			if (abs($property->top - $top) < Generator::$precision) {
				$this->properties[$column] = $property;
			}
		}
		foreach ($this->group->elements as $column => $element) {
			if (abs($element->top - $top) < Generator::$precision) {
				$this->properties[] = $element;
				$added_elements = true;
			}
		}
		if (isset($added_elements)) {
			usort($this->properties, function(Field $property1, Field $property2) {
				return cmp($property1->hotX(), $property2->hotX());
			});
		}
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
	 * @input $structure->pages->elements, $structure->pages->groups
	 * @output $elements, $groups
	 */
	public function run()
	{
		foreach ($this->structure->pages as $page) {
			$this->elements =& $page->elements;
			foreach ($page->groups as $group) {
				$this->group = $group;
				$this->runGroup();
			}
		}
	}

	//-------------------------------------------------------------------------------------- runGroup
	/**
	 * @input $elements, $group
	 */
	protected function runGroup()
	{
		if (!$this->group->iterations) {
			return;
		}
		$this->properties();
		$this->emptyColumns();
		if (!$this->unset) {
			return;
		}
		$this->headers();
		$this->shiftsWidths();
		$this->applyShiftsWidths($this->headers);
		foreach ($this->group->iterations as $iteration) {
			$this->applyShiftsWidths($iteration->elements);
			$this->removeElements($iteration->elements);
		}
	}

	//---------------------------------------------------------------------------------- shiftsWidths
	/**
	 * Calculate column shifts / widths increment
	 *
	 * @input $properties, $unset
	 * @output $shifts, $widths
	 */
	protected function shiftsWidths()
	{
		$property = end($this->unset);
		$column   = key($this->unset);
		$right    = ($column === (count($this->properties) - 1))
			? $property->right()
			: $this->properties[$column + 1]->left;
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
				$set_property = $this->properties[$column];
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
