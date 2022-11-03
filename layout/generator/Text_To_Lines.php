<?php
namespace ITRocks\Framework\Layout\Generator;

use ITRocks\Framework\AOP\Joinpoint\After_Method;
use ITRocks\Framework\Layout\Generator;
use ITRocks\Framework\Layout\Structure\Draw\Horizontal_Line;
use ITRocks\Framework\Layout\Structure\Element;
use ITRocks\Framework\Layout\Structure\Field\Text;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;

/**
 * @feature Replace special printed values by drawings
 */
class Text_To_Lines implements Registerable
{

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Register
	 */
	public function register(Register $register)
	{
		$register->aop->afterMethod([Generator::class, 'generate'], [$this, 'textToLines']);
	}

	//---------------------------------------------------------------------------- textElementsToLine
	/**
	 * @param $elements Element[]
	 */
	protected function textElementsToLine(array &$elements)
	{
		foreach ($elements as $key => $element) {
			if (!($element instanceof Text)) {
				continue;
			}
			// ==... => double-line
			if (str_starts_with(ltrim($element->text), '==')) {
				if (str_replace(['=', SP], '', $element->text)) {
					continue;
				}
				$elements[$key] = $this->toHorizontalLine($element, -.3);
				$elements[]     = $this->toHorizontalLine($element, .3);
			}
			// --... => simple-line
			if (str_starts_with(ltrim($element->text), '--')) {
				if (str_replace(['-', SP], '', $element->text)) {
					continue;
				}
				$elements[$key] = $this->toHorizontalLine($element);
			}
		}
	}

	//----------------------------------------------------------------------------------- textToLines
	/**
	 * @param $joinpoint After_Method
	 */
	public function textToLines(After_Method $joinpoint)
	{
		/** @var $generator Generator */
		$generator = $joinpoint->object;
		foreach ($generator->structure->pages as $page) {
			$this->textElementsToLine($page->elements);
			foreach ($page->groups as $group) {
				foreach ($group->iterations as $iteration) {
					$this->textElementsToLine($iteration->elements);
				}
			}
		}
	}

	//------------------------------------------------------------------------------ toHorizontalLine
	/**
	 * @param $element        Element
	 * @param $vertical_shift integer
	 * @return Element
	 */
	public function toHorizontalLine(Element $element, int $vertical_shift = 0) : Element
	{
		$line = new Horizontal_Line($element->page);
		$line->group     = $element->group;
		$line->iteration = $element->iteration;
		$line->left      = $element->left;
		$line->top       = $element->top + ($element->height / 2) + $vertical_shift;
		$line->height    = 0;
		$line->width     = $element->width;
		return $line;
	}

}
