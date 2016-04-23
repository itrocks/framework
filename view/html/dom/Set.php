<?php
namespace SAF\Framework\View\Html\Dom;

use SAF\Framework\Locale\Loc;

/**
 * A DOM element class for HTML form set inputs
 */
class Set extends Element
{

	//------------------------------------------------------------------------------------ $base_name
	/**
	 * @var string
	 */
	private $base_name;

	//------------------------------------------------------------------------------------- $selected
	/**
	 * @var string[]
	 */
	private $selected = [];

	//--------------------------------------------------------------------------------------- $values
	/**
	 * @var string[]
	 */
	private $values = [];

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $base_name string The base name for all input fields named 'base_name[value]'
	 * @param $values    string[]
	 * @param $selected  string
	 * @param $id        string
	 */
	public function __construct($base_name = null, $values = null, $selected = null, $id = null)
	{
		parent::__construct('span', true);
		$this->setAttribute('class', 'set');
		if (isset($id))         $this->setAttribute('id',   $id);
		if (isset($base_name))  $this->base_name = $base_name;
		if (isset($values))     $this->values = $values;
		if (isset($selected))   $this->selected($selected);
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
		$this->setContent(null);
	}

	//------------------------------------------------------------------------------------ getContent
	/**
	 * The getter for $content
	 * Work in progress
	 *
	 * @return string
	 */
	public function getContent()
	{
		$content = parent::getContent();
		if (!isset($content)) {
			$values = $this->values;
			asort($values);
			if (isset($values[''])) {
				unset($values['']);
			}
			$content = '';
			$selected = $this->selected();

			foreach ($values as $value => $caption) {
				$html_option = new Input($this->base_name . '[]', $value);
				$html_option->setAttribute('type', 'checkbox');
				if (in_array($value, $selected)) {
					$html_option->setAttribute('checked');
				}
				$content .= strval(new Label(strval($html_option) . Loc::tr($caption))) . BR;
			}
			$this->setContent($content);
		}
		return $content;
	}

	//-------------------------------------------------------------------------------------- selected
	/**
	 * @param $selected string if not set, selected will return current value without removing it
	 * @return string[]
	 */
	public function selected($selected = null)
	{
		if (isset($selected)) {
			if (is_array($selected)) {
				$this->selected = $selected;
			}
			else {
				$this->selected = explode(',', $selected);
			}
		}
		return $this->selected;
	}

}
