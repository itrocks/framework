<?php
namespace ITRocks\Framework\Component;

use ITRocks\Framework\View\Html\Template;

/**
 * Class Breadcrumb
 */
class Breadcrumb
{

	//-------------------------------------------------------------------------------- COMPONENT_NAME
	const COMPONENT_NAME = 'breadcrumb';

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
	public string $title = "";

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
		$this->buttons = $buttons;
		$this->back_link = $back_link;
		$this->title = $title;
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		$template = new Template(null, static::TEMPLATE_PATH);
		$template->setParameters(
			['title' => $this->title, 'buttons' => $this->buttons, 'back_link' => $this->back_link]
		);
		return $template->parse();
	}

	//------------------------------------------------------------------------------------- addButton
	public function addButton(string $caption, string $link = '', string $class = '') : self
	{
		$this->buttons[] = new Button($caption, $link, null, [Button::CLASS => $class]);
		return $this;
	}

	//----------------------------------------------------------------------------------- setBackLink
	public function setBackLink(string $link = '', string $class = '') : self
	{
		$this->back_link = new Button('', $link, null, [Button::CLASS => $class]);
		return $this;
	}

}
