<?php
namespace ITRocks\Framework\Layout\Structure\Group;

use ITRocks\Framework\Layout\Generator;
use ITRocks\Framework\Layout\Structure\Element;
use ITRocks\Framework\Layout\Structure\Field;
use ITRocks\Framework\Layout\Structure\Group;
use ITRocks\Framework\Layout\Structure\Page;
use ITRocks\Framework\Reflection\Attribute\Property\Mandatory;

/**
 * An iteration is a "line" (or column) of data into a group
 */
class Iteration extends Element
{

	//----------------------------------------------------------------------------------- DUMP_SYMBOL
	const DUMP_SYMBOL = 'x';

	//------------------------------------------------------------------------------------- $elements
	/** @var Element[] */
	#[Mandatory]
	public array $elements;

	//--------------------------------------------------------------------------------------- $number
	/** Iteration number : 0..n */
	public int $number;

	//-------------------------------------------------------------------------------------- $spacing
	/** Apply group iteration spacing ? */
	public bool $spacing = true;

	//------------------------------------------------------------------------------- calculateHeight
	/**
	 * Calculate the maximum height occupied by included elements
	 *
	 * This takes care of the top position of the highest element : this this the total height of the
	 * line
	 *
	 * @output $height
	 */
	public function calculateHeight() : float
	{
		$iteration_bottom = .0;
		$iteration_margin = .0;
		$iteration_top    = reset($this->elements)->top;
		$line_bottom      = .0;
		$line_top         = .0;
		foreach ($this->elements as $element) {
			if ($element->top > $line_top) {
				if ($line_bottom) {
					$iteration_margin = max($iteration_margin, $element->top - $line_bottom);
					$line_bottom      = .0;
				}
				$line_top = $element->top;
			}
			$line_bottom      = max($line_bottom, $element->top + $element->height);
			$iteration_bottom = max($iteration_bottom, $line_bottom);
		}
		return $this->height = $iteration_bottom + $iteration_margin - $iteration_top;
	}

	//------------------------------------------------------------------------------ cloneWithContext
	public function cloneWithContext(Page $page, Group $group = null, Iteration $iteration = null)
		: static
	{
		/** @var $iteration Iteration PhpStorm bug */
		$iteration = parent::cloneWithContext($page, $group, $iteration);

		$elements = [];
		foreach ($this->elements as $element) {
			$elements[] = $element->cloneWithContext($page, $group, $iteration);
		}
		$this->elements = $elements;

		return $iteration;
	}

	//------------------------------------------------------------------------------------------ down
	/**
	 * Move the iteration and all contained elements down
	 *
	 * @param $height        float   The distance to move (mm)
	 * @param $elements_only boolean If true, the iteration does not go up : only elements
	 */
	public function down(float $height, bool $elements_only = false) : void
	{
		$this->up(-$height, $elements_only);
	}

	//------------------------------------------------------------------------------------------ dump
	public function dump(int $level = 0) : string
	{
		$dump = parent::dump($level) . SP . '(' . $this->number . ')' . LF;
		foreach ($this->elements as $element) {
			$dump .= $element->dump($level + 1) . LF;
		}
		return $dump;
	}

	//------------------------------------------------------------------------------- sortElementsByY
	/** Sort elements from upper to lower position, then from left to right */
	public function sortElementsByY()
	{
		usort($this->elements, function(Field $element1, Field $element2) {
			return (abs($element1->top - $element2->top) > Generator::$precision)
				? cmp($element1->top, $element2->top)
				: cmp($element1->hotX(), $element2->hotX());
		});
	}

	//--------------------------------------------------------------------------------------- spacing
	public function spacing() : float
	{
		return $this->spacing ? $this->group->iteration_spacing : .0;
	}

	//-------------------------------------------------------------------------------------------- up
	/**
	 * Move the iteration and all contained elements up
	 *
	 * @param $height        float   The distance to move (mm)
	 * @param $elements_only boolean If true, the iteration does not go up : only elements
	 */
	public function up(float $height, bool $elements_only = false) : void
	{
		if (!$elements_only) {
			$this->top -= $height;
		}
		foreach ($this->elements as $element) {
			$element->top -= $height;
		}
	}

}
