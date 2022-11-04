<?php
namespace ITRocks\Framework\View\Html\Dom;

/**
 * A DOM element class for HTML form inputs <input>
 */
class Input extends Element
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $name  string|null
	 * @param $value string|null
	 * @param $id    string|null
	 */
	public function __construct(string $name = null, string $value = null, string $id = null)
	{
		parent::__construct('input', false);
		if (isset($name))  $this->setAttribute('name',  $name);
		if (isset($value)) $this->setAttribute('value', $value);
		if (isset($id))    $this->setAttribute('id',    $id);
	}

	//---------------------------------------------------------------------------------- setAttribute
	/**
	 * @param $name  string
	 * @param $value boolean|integer|string
	 * @return Attribute
	 */
	public function setAttribute(string $name, bool|int|string $value = '') : Attribute
	{
		if (($name === 'value') && is_string($value)) {
			$value = str_replace(['{', '}'], ['&lbrace;', '&rbrace;'], $value);
		}
		return parent::setAttribute($name, $value);
	}

}
