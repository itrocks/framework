<?php
namespace ITRocks\Framework\View\Html\Dom;

/**
 * A DOM element class for HTML form select inputs <select><option>...</select>
 */
class Select extends Element
{

	//-------------------------------------------------------------------------------------- $ordered
	/**
	 * If true, values are ordered and should not be sorted.
	 * If false, do not care of values order : they will be sorted alphabetically.
	 *
	 * @var boolean
	 */
	public $ordered = false;

	//------------------------------------------------------------------------------------- $selected
	/**
	 * @var string
	 */
	private $selected;

	//--------------------------------------------------------------------------------------- $values
	/**
	 * @var string[]
	 */
	public $values = [];

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $name     string
	 * @param $values   string[]
	 * @param $selected string
	 * @param $id       string
	 */
	public function __construct($name = null, array $values = null, $selected = null, $id = null)
	{
		parent::__construct('select', true);
		if (isset($id))       $this->setAttribute('id',   $id);
		if (isset($name))     $this->setAttribute('name', $name);
		if (isset($values))   $this->values = $values;
		if (isset($selected)) $this->selected($selected);
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
	 *
	 * @return string
	 */
	public function getContent()
	{
		$content = parent::getContent();
		if (!isset($content)) {
			$values = $this->values;
			if (!$this->ordered) {
				asort($values);
			}
			if (isset($values[''])) {
				$value = $values[''];
				unset($values['']);
				$values = ['' => $value] + $values;
			}
			$content  = '';
			$selected = $this->selected();
			/** @var $selected_option Option */
			$selected_option = null;
			foreach ($values as $value => $caption) {
				$html_option = new Option($value, $caption);
				if ($value === $selected) {
					$html_option->setAttribute('selected');
					if ($selected_option) {
						$selected_option->removeAttribute('selected');
					}
				}
				if (($html_option->getContent() == $selected) && !$selected_option) {
					$html_option->setAttribute('selected');
					$selected_option = $html_option;
				}
				$content .= strval($html_option);
			}
			$this->setContent($content);
		}
		return $content;
	}

	//-------------------------------------------------------------------------------------- selected
	/**
	 * @param $selected string if not set, selected will return current value without removing it
	 * @return string
	 */
	public function selected($selected = null)
	{
		if (isset($selected)) {
			$this->selected = $selected;
		}
		return $this->selected;
	}

}
