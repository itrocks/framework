<?php
namespace ITRocks\Framework\View\Html\Dom;

/**
 * A DOM element class for HTML forms texteareas <textarea>
 */
class Textarea extends Element
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $name  string|null
	 * @param $value string|null
	 */
	public function __construct(string $name = null, string $value = null)
	{
		parent::__construct('textarea');
		if (isset($name))  $this->setAttribute('name', $name);
		if (isset($value)) $this->setContent(
			strReplace(['<' => '&lt;', '>' => '&gt;', '|' => '&#124;'], $value)
		);
	}

}
