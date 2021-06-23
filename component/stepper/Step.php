<?php
namespace ITRocks\Framework\Component\Stepper;

use ITRocks\Framework\Controller\Target;

/**
 * Class Step
 */
class Step
{

	//-------------------------------------------------------------------------------------- $caption
	public string $caption = '';

	//------------------------------------------------------------------------------------ $css_class
	/**
	 * @getter
	 * @var string
	 */
	public string $css_class = '';

	//-------------------------------------------------------------------------------------- $current
	public bool $current = false;
	//------------------------------------------------------------------------------------ $data_post
	public array $data_post = [];

	//-------------------------------------------------------------------------------------- $is_done
	public bool $is_done = false;

	//----------------------------------------------------------------------------------------- $link
	public string $link = '';

	//----------------------------------------------------------------------------------- $sort_order
	public int $sort_order = 0;

	//--------------------------------------------------------------------------------------- $target
	public string $target = Target::MAIN;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(
		int $sort_order, string $caption, string $link = '', string $target = Target::MAIN, string $css_class = '',
		array $data_post = [], $current = false
	) {
		$this->sort_order = $sort_order;
		$this->caption   = $caption;
		$this->link      = $link;
		$this->target    = $target;
		$this->css_class = $css_class;
		$this->data_post = $data_post;
		$this->current   = $current;
	}

	//----------------------------------------------------------------------------------- getCssClass
	/**
	 * @return string
	 */
	public function getCssClass() : string
	{
		return $this->css_class . SP .
			($this->is_done ? 'step-done' : '') . SP .
			($this->current ? 'step-active' : '');
	}

}
