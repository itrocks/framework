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

	//--------------------------------------------------------------------------- $additional_classes
	/**
	 * @var string[]
	 */
	public array $additional_classes;

	//---------------------------------------------------------------------------------------- $color
	/**
	 * @var string
	 */
	public string $color;

	//-------------------------------------------------------------------------------------- $content
	/**
	 * @var string
	 */
	public string $content = '';

	//------------------------------------------------------------------------------------ $data_post
	/**
	 * @var string[]
	 */
	public array $data_post = [];

	//----------------------------------------------------------------------------------------- $hint
	/**
	 * @var string
	 */
	public string $hint = '';

	//----------------------------------------------------------------------------------------- $link
	/**
	 * @var string
	 */
	public string $link = '';

	//--------------------------------------------------------------------------------------- $target
	/**
	 * @var string
	 */
	public string $target = '';

	//-------------------------------------------------------------------------------------- $url_app
	/**
	 * The method used to generate link. Default is app:// -> <a href="app://(link)>
	 *
	 * @var string
	 */
	public string $url_app;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Button constructor.
	 *
	 * @param $content            string
	 * @param $hint               string
	 * @param $link               string
	 * @param $target             string
	 * @param $data_post          array
	 * @param $color              string
	 * @param $additional_classes array
	 * @param $url_app            string
	 */
	public function __construct(
		string $content = '', string $hint = '', string $link = '', string $target = Target::MAIN,
		array $data_post = [], string $color = '', array $additional_classes = [],
		string $url_app = 'app://'
	) {
		$this->additional_classes = $additional_classes;
		$this->color              = $color ?? static::COLOR_SECONDARY;
		$this->content            = $content;
		$this->data_post          = $data_post;
		$this->hint               = $hint;
		$this->link               = $link;
		$this->target             = $target;
		$this->url_app            = $url_app;
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		$template        = new Template(null, static::TEMPLATE_PATH);
		$data_post_array = [];
		foreach ($this->data_post as $key => $data) {
			$data_post_array[] = $key . '=' . $data;
		}
		$template->setParameters(
			[
				Parameter::IS_INCLUDED => true,
				'additional_classes'   => join(' ', $this->additional_classes),
				'color'                => $this->color,
				'content'              => $this->content,
				'data-post'            => join(',', $data_post_array),
				'hint'                 => $this->hint,
				'link'                 => $this->link,
				'target'               => $this->target,
			]
		);
		return $template->parse();
	}

}
