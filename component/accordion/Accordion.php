<?php
namespace ITRocks\Framework\Component;

use ITRocks\Framework\Controller\Parameter;
use ITRocks\Framework\View\Html\Dom\Element;
use ITRocks\Framework\View\Html\Template;

/**
 * Class Accordion
 */
class Accordion
{

	//-------------------------------------------------------------------------------- COMPONENT_NAME
	const COMPONENT_NAME = 'accordion';

	//--------------------------------------------------------------------------------- TEMPLATE_PATH
	const TEMPLATE_PATH = 'itrocks/framework/component/accordion/accordion.html';

	//------------------------------------------------------------------------------------- $contents
	/**
	 * @var Element[]
	 */
	public array $contents;

	//--------------------------------------------------------------------------------- $icon_classes
	/**
	 * @var string[]
	 */
	public array $icon_classes;

	//---------------------------------------------------------------------------------- $input_label
	/**
	 * @var string
	 */
	public string $input_label;

	//----------------------------------------------------------------------------------- $input_name
	/**
	 * @var string
	 */
	public string $input_name;

	//---------------------------------------------------------------------------------- $input_value
	/**
	 * @var string
	 */
	public string $input_value;

	//----------------------------------------------------------------------------------------- $name
	/**
	 * @var string
	 */
	public string $name;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Accordion constructor.
	 *
	 * @param $name         string
	 * @param $input_name   string
	 * @param $input_value  string
	 * @param $input_label  string
	 * @param $icon_classes string[]
	 * @param $contents     Element[]
	 */
	public function __construct(
		string $name, string $input_name, string $input_value, string $input_label = '',
		array $icon_classes = [], array $contents = []
	) {
		$this->name         = $name;
		$this->input_name   = $input_name;
		$this->input_value  = $input_value;
		$this->input_label  = $input_label;
		$this->icon_classes = $icon_classes;
		$this->contents     = $contents;
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
				'name'                 => $this->name,
				'input_name'           => $this->input_name,
				'input_value'          => $this->input_value,
				'input_label'          => $this->input_label,
				'icon_classes'         => $this->icon_classes,
				'contents'             => $this->contents
			]
		);
		return $template->parse();
	}

}
