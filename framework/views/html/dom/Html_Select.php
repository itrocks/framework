<?php
namespace SAF\Framework;

/**
 * A DOM element class for HTML form select inputs <select><option>...</select>
 *
 * @use content
 */
class Html_Select extends Dom_Element
{

	//-------------------------------------------------------------------------------------- $content
	/**
	 * @getter getContent
	 * @override
	 * @var string[]
	 */
	public $content;

	//--------------------------------------------------------------------------------------- $values
	/**
	 * @var Html_Option[]
	 */
	private $values = array();

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $name   string
	 * @param $values string[]
	 * @param $value  string
	 * @param $id     string
	 */
	public function __construct($name = null, $values = null, $value = null, $id = null)
	{
		parent::__construct("select", true);
		if (isset($name))   $this->setAttribute("name",   $name);
		if (isset($values)) $this->values = $values;
		if (isset($value))  $this->setAttribute("value",  $value);
		if (isset($id))     $this->setAttribute("id",     $id);
	}

	//-------------------------------------------------------------------------------------- addValue
	/**
	 * Adds a value
	 *
	 * @param $value   string
	 * @param $caption string
	 */
	public function addValue($value, $caption = null)
	{
		$this->values[$value] = isset($caption) ? $caption : $value;
		unset($this->content);
	}

	//------------------------------------------------------------------------------------ getContent
	/**
	 * The getter for $content
	 *
	 * @return string
	 */
	public function getContent()
	{
		if (isset($this->content)) {
			return $this->content;
		}
		else {
			$values = $this->values;
			asort($values);
			$content = "";
			foreach ($values as $value => $caption) {
				$content .= strval(new Html_Option($value, $caption));
			}
			$this->content = $content;
			return $content;
		}
	}

}
