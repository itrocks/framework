<?php
namespace ITRocks\Framework\View\Html\Dom;

/**
 * A DOM element class for HTML buttons <button>
 */
class Button extends Element
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value string|null
	 * @param $id    string|null
	 */
	public function __construct(string $value = null, string $id = null)
	{
		parent::__construct('button', false);
		if (isset($value)) $this->setContent($value);
		if (isset($id))    $this->setAttribute('id', $id);
	}

}
