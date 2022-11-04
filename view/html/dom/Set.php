<?php
namespace ITRocks\Framework\View\Html\Dom;

use ITRocks\Framework\Locale\Loc;

/**
 * A DOM element class for HTML form set inputs
 */
class Set extends Element
{

	//------------------------------------------------------------------------------------ $base_name
	/**
	 * @var string
	 */
	private string $base_name = '';

	//------------------------------------------------------------------------------- $ordered_values
	/**
	 * @var boolean
	 */
	private bool $ordered_values = false;

	//------------------------------------------------------------------------------------- $readonly
	/**
	 * @var boolean
	 */
	private bool $readonly = false;

	//------------------------------------------------------------------------------------- $selected
	/**
	 * @var string[]
	 */
	private array $selected = [];

	//--------------------------------------------------------------------------------------- $values
	/**
	 * @var string[]
	 */
	private array $values = [];

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $base_name      string|null The base name for all input fields named 'base_name[value]'
	 * @param $values         string[]|null
	 * @param $selected       string|null
	 * @param $id             string|null
	 * @param $readonly       boolean|null
	 * @param $ordered_values boolean|null
	 */
	public function __construct(
		string $base_name = null, array $values = null, string $selected = null, string $id = null,
		bool $readonly = false, bool $ordered_values = false
	) {
		parent::__construct('span');
		$this->setAttribute('class', 'set');
		if (isset($base_name))      $this->base_name = $base_name;
		if (isset($id))             $this->setAttribute('id', $id);
		if (isset($ordered_values)) $this->ordered_values = $ordered_values;
		if (isset($readonly))       $this->readonly       = $readonly;
		if (isset($selected))       $this->selected($selected);
		if (isset($values))         $this->values = $values;
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
	 * Work in progress
	 *
	 * @return string
	 */
	public function getContent() : string
	{
		$content = parent::getContent();
		// TODO HCR Something more simple (BP : why removeAttribute ?)
		$conditions = $this->getData('conditions');
		if ($conditions) {
			$this->removeData('conditions');
		}
		if (!isset($content)) {
			$values = $this->values;
			if (!$this->ordered_values) {
				asort($values);
			}
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
				if ($this->readonly) {
					$html_option->removeAttribute('name');
					$html_option->setAttribute('disabled');
					$html_option->setAttribute('readonly');
				}
				$label = new Label($html_option . new Span(Loc::tr($caption)));
				$label->setAttribute('name', $this->base_name);
				if ($conditions) {
					$label->setAttribute($conditions->name, $conditions->value);
				}
				$content .= $label . LF;
			}
			$this->setContent(trim($content));
		}
		return $content;
	}

	//-------------------------------------------------------------------------------------- selected
	/**
	 * @param $selected array|string|null if not set, selected will return current value without
	 *                  removing it
	 * @return string[]
	 */
	public function selected(array|string $selected = null) : array
	{
		if (isset($selected)) {
			$this->selected = is_array($selected) ? $selected : explode(',', $selected);
		}
		return $this->selected;
	}

}
