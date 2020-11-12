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
	private $base_name;

	//------------------------------------------------------------------------------- $ordered_values
	/**
	 * @var boolean
	 */
	private $ordered_values = false;

	//------------------------------------------------------------------------------------- $readonly
	/**
	 * @var boolean
	 */
	private $readonly = false;

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
	 * @param $base_name      string The base name for all input fields named 'base_name[value]'
	 * @param $values         string[]
	 * @param $selected       string
	 * @param $id             string
	 * @param $readonly       boolean
	 * @param $ordered_values boolean
	 */
	public function __construct(
		$base_name = null, array $values = null, $selected = null, $id = null, $readonly = false,
		$ordered_values = false
	) {
		parent::__construct('span', true);
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
				$label = new Label(strval($html_option) . strval(new Span(Loc::tr($caption))));
				$label->setAttribute('name', $this->base_name);
				if ($conditions) {
					$label->setAttribute($conditions->name, $conditions->value);
				}
				$content .= strval($label) . LF;
			}
			$this->setContent(trim($content));
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
