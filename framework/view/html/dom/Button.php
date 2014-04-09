<?php
namespace SAF\Framework\View\Html\Dom;

/**
 * A DOM element class for HTML buttons <button>
 */
class Button extends Element
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value string
	 * @param $id string
	 */
	public function __construct($value = null, $id = null)
	{
		parent::__construct('button', false);
		if (isset($value)) $this->setContent($value);
		if (isset($id))    $this->setAttribute('id', $id);
	}

}
