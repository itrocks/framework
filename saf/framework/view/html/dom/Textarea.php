<?php
namespace SAF\Framework\View\Html\Dom;

/**
 * A DOM element class for HTML forms texteareas <textarea>
 */
class Textarea extends Element
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $name  string
	 * @param $value string
	 */
	public function __construct($name = null, $value = null)
	{
		parent::__construct('textarea');
		if (isset($name))  $this->setAttribute('name', $name);
		if (isset($value)) $this->setContent($value);
	}

}
