<?php
namespace ITRocks\Framework\Component;

use ITRocks\Framework\Component\Stepper\Step;
use ITRocks\Framework\Controller\Parameter;
use ITRocks\Framework\Controller\Target;
use ITRocks\Framework\View\Html\Template;

/**
 * Class Stepper
 */
class Stepper
{

	//--------------------------------------------------------------------------------- TEMPLATE_PATH
	const TEMPLATE_PATH = 'itrocks/framework/component/stepper/stepper.html';

	//---------------------------------------------------------------------------------------- $steps
	/**
	 * @var Step[]
	 */
	public array $steps = [];

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		$template = new Template(null, static::TEMPLATE_PATH);
		$template->setParameters([Parameter::IS_INCLUDED => true, 'steps' => $this->getSortedSteps()]);
		return $template->parse();
	}

	//--------------------------------------------------------------------------------------- addStep
	/**
	 * @param $sort_order integer
	 * @param $caption    string
	 * @param $link       string
	 * @param $css_class  string
	 * @param $target     string
	 * @param $data_post  array
	 * @param $current    boolean
	 * @return static
	 */
	public function addStep(
		int $sort_order, string $caption, string $link = '', string $css_class = '',
		string $target = Target::MAIN, array $data_post = [], bool $current = false
	) : static
	{
		$this->steps[] = new Step(
			$sort_order, $caption, $link, $target, $css_class, $data_post, $current
		);
		return $this;
	}

	//-------------------------------------------------------------------------------- getSortedSteps
	/**
	 * @return Step[]
	 */
	public function getSortedSteps() : array
	{
		usort(

			$this->steps, function (Step $step_a, Step $step_b) {
				return $step_a->sort_order - $step_b->sort_order;
			}
		);
		$this->markDoneSteps();
		return $this->steps;
	}

	//--------------------------------------------------------------------------------- markDoneSteps
	protected function markDoneSteps()
	{
		$current_step = reset($this->steps);
		foreach ($this->steps as $step) {
			if ($step->current) {
				$current_step = $step;
			}
		}
		foreach ($this->steps as $step) {
			if ($step !== $current_step && $step->sort_order < $current_step->sort_order) {
				$step->is_done = true;
			}
		}
	}

}
