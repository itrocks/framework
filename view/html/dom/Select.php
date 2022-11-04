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
	public bool $ordered = false;

	//------------------------------------------------------------------------------------- $selected
	/**
	 * @var string
	 */
	private string $selected = '';

	//--------------------------------------------------------------------------------------- $values
	/**
	 * @var string[]
	 */
	public array $values = [];

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $name     string|null
	 * @param $values   string[]|null
	 * @param $selected string|null
	 * @param $id       string|null
	 */
	public function __construct(
		string $name = null, array $values = null, string $selected = null, string $id = null
	) {
		parent::__construct('select');
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
	 * @param $caption string|null
	 */
	public function addValue(string $value, string $caption = null) : void
	{
		$this->values[$value] = $caption ?? $value;
		$this->setContent(null);
	}

	//------------------------------------------------------------------------------------ getContent
	/**
	 * The getter for $content
	 *
	 * @return string
	 */
	public function getContent() : string
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
					$selected_option?->removeAttribute('selected');
				}
				if (($html_option->getContent() === $selected) && !$selected_option) {
					$html_option->setAttribute('selected');
					$selected_option = $html_option;
				}
				$content .= $html_option;
			}
			$this->setContent($content);
		}
		return $content;
	}

	//-------------------------------------------------------------------------------------- selected
	/**
	 * @param $selected string|null if not set, selected will return current value without removing it
	 * @return string
	 */
	public function selected(string $selected = null) : string
	{
		if (isset($selected)) {
			$this->selected = $selected;
		}
		return $this->selected;
	}

}
