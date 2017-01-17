<?php
namespace ITRocks\Framework\View\Html\Dom;

/**
 * A DOM element class for HTML form inputs <input>
 */
class Input extends Element
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $name  string
	 * @param $value string
	 * @param $id    string
	 */
	public function __construct($name = null, $value = null, $id = null)
	{
		parent::__construct('input', false);
		if (isset($name))  $this->setAttribute('name',  $name);
		if (isset($value)) $this->setAttribute('value', $value);
		if (isset($id))    $this->setAttribute('id',    $id);
	}

	//---------------------------------------------------------------------------------- setAttribute
	/**
	 * @param $name  string
	 * @param $value string
	 * @return Attribute
	 */
	public function setAttribute($name, $value = null)
	{
		if (($name === 'value') && is_string($value)) {
			$value = str_replace(['{', '}'], ['&lbrace;', '&rbrace;'], $value);
		}
		return parent::setAttribute($name, $value);
	}

}
