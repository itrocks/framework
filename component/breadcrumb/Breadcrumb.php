<?php
namespace ITRocks\Framework\Component;

use ITRocks\Framework\Controller\Parameter;
use ITRocks\Framework\View\Html\Template;

/**
 * Class Breadcrumb
 */
class Breadcrumb
{

	//--------------------------------------------------------------------------------- TEMPLATE_PATH
	const TEMPLATE_PATH = 'itrocks/framework/component/breadcrumb/breadcrumb.html';

	//------------------------------------------------------------------------------------ $back_link
	/**
	 * @var ?Button
	 */
	public ?Button $back_link = null;

	//-------------------------------------------------------------------------------------- $buttons
	/**
	 * @var Button[]
	 */
	public array $buttons;

	//---------------------------------------------------------------------------------------- $title
	/**
	 * @var string
	 */
	public string $title = '';

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Breadcrumb constructor.
	 *
	 * @param $title     string
	 * @param $back_link Button|null
	 * @param $buttons   array
	 */
	public function __construct(string $title, ?Button $back_link = null, array $buttons = [])
	{
		$this->back_link = $back_link;
		$this->buttons   = $buttons;
		$this->title     = $title;
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		$template = new Template(null, static::TEMPLATE_PATH);
		$template->setParameters(
			[
				Parameter::IS_INCLUDED => true,
				'back_link'            => $this->back_link,
				'buttons'              => $this->buttons,
				'title'                => $this->title
			]
		);
		return $template->parse();
	}

}
