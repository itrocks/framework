<?php
namespace ITRocks\Framework\Component\Button_List;

use ITRocks\Framework\Controller\Parameter;
use ITRocks\Framework\Controller\Target;
use ITRocks\Framework\View\Html\Template;

/**
 * Class Button
 */
class Button
{

	//--------------------------------------------------------------------------------- COLOR_PRIMARY
	const COLOR_PRIMARY = 'color-azure';

	//------------------------------------------------------------------------------- COLOR_SECONDARY
	const COLOR_SECONDARY = 'color-very-light-pink-four';

	//--------------------------------------------------------------------------------- TEMPLATE_PATH
	const TEMPLATE_PATH = 'itrocks/framework/component/button_list/button.html';
	
	//---------------------------------------------------------------------------------------- $color
	public string $color;

	//-------------------------------------------------------------------------------------- $content
	public string $content = '';

	//------------------------------------------------------------------------------------ $data_post
	public array $data_post = [];

	//----------------------------------------------------------------------------------------- $hint
	public string $hint = '';

	//----------------------------------------------------------------------------------------- $link
	public string $link = '';

	//--------------------------------------------------------------------------------------- $target
	public string $target = '';

	//----------------------------------------------------------------------------------- __construct
	public function __construct(
		string $content = '', string $hint = '', string $link = '', string $target = Target::MAIN,
		array $data_post = [], string $color = ''
	) {
		$this->content   = $content;
		$this->hint      = $hint;
		$this->link      = $link;
		$this->target    = $target;
		$this->content   = $content;
		$this->data_post = $data_post;
		$this->color     = $color ?? static::COLOR_SECONDARY;
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		$template = new Template(null, static::TEMPLATE_PATH);
		$data_post_array = [];
		foreach ($this->data_post as $key => $data) {
			$data_post_array[] = $key . '=' . $data;
		}
		$template->setParameters(
			[
				Parameter::IS_INCLUDED => true,
				'color'                => $this->color,
				'content'              => $this->content,
				'data-post'            => join(',',$data_post_array),
				'hint'                 => $this->hint,
				'link'                 => $this->link,
				'target'               => $this->target,
			]
		);
		return $template->parse();
	}

}
